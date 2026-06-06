<?php

namespace App\Filament\Resources\PayrollRuns\Tables;

use App\Exports\PaymentAdviceExport;
use App\Exports\PayrollCompleteExport;
use App\Exports\PayrollSummaryExport;
use App\Imports\BankStatementImport;
use App\Jobs\SendPaySlipsJob;
use App\Models\PayrollRun;
use App\Services\PayrollService;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Maatwebsite\Excel\Facades\Excel;

class PayrollRunsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([

                TextColumn::make('period_label')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('period_start')
                    ->date('d M Y'),

                TextColumn::make('period_end')
                    ->date('d M Y'),

                TextColumn::make('total_gross')
                    ->label('Gross')
                    ->money('INR'),

                TextColumn::make('total_deductions')
                    ->label('Deductions')
                    ->money('INR'),

                TextColumn::make('total_net')
                    ->label('Net pay')
                    ->money('INR')
                    ->weight('bold'),

                TextColumn::make('paid_at')
                    ->label('Paid on')
                    ->date('d M Y')
                    ->placeholder('Not paid yet'),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft'                => 'gray',
                        'processing'           => 'warning',
                        'approved', 'paid'     => 'success',
                        default                => 'gray',
                    }),
            ])

            ->actions([

                // PROCESS
                Action::make('process')
                    ->label('Process payroll')
                    ->icon('heroicon-o-cog-6-tooth')
                    ->color('warning')
                    ->visible(fn (PayrollRun $record) => $record->isDraft())
                    ->requiresConfirmation()
                    ->action(function (PayrollRun $record) {
                        app(PayrollService::class)->process($record);
                        Notification::make()
                            ->title('Payroll processed successfully')
                            ->success()
                            ->send();
                    }),

                // APPROVE
                Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (PayrollRun $record) => $record->status === 'processing')
                    ->requiresConfirmation()
                    ->action(function (PayrollRun $record) {
                        $record->update([
                            'status'      => 'approved',
                            'approved_at' => now(),
                        ]);
                        $record->paySlips()->update(['status' => 'approved']);
                        Notification::make()
                            ->title('Payroll approved')
                            ->success()
                            ->send();
                    }),

                // SEND PAY SLIPS
                Action::make('send_slips')
                    ->label('Send pay slips')
                    ->icon('heroicon-o-envelope')
                    ->color('info')
                    ->visible(fn (PayrollRun $record) => $record->isApproved())
                    ->requiresConfirmation()
                    ->action(function (PayrollRun $record) {
                        SendPaySlipsJob::dispatch($record);
                        Notification::make()
                            ->title('Pay slips queued for delivery')
                            ->success()
                            ->send();
                    }),

                // MARK AS PAID
                Action::make('mark_paid')
                    ->label('Mark as paid')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->visible(fn (PayrollRun $record) => $record->isApproved())
                    ->requiresConfirmation()
                    ->modalHeading('Mark payroll as paid')
                    ->modalDescription('Confirm salaries have been credited to all employee accounts.')
                    ->form([
                        Select::make('payment_mode')
                            ->label('Payment mode used')
                            ->options([
                                'NEFT'   => 'NEFT',
                                'RTGS'   => 'RTGS',
                                'IMPS'   => 'IMPS',
                                'cheque' => 'Cheque',
                            ])
                            ->required(),

                        TextInput::make('payment_reference')
                            ->label('Bank transaction reference')
                            ->placeholder('e.g. NEFT20260528001')
                            ->helperText('Optional — from your bank confirmation'),

                        DatePicker::make('paid_at')
                            ->label('Payment date')
                            ->default(today())
                            ->required(),
                    ])
                    ->action(function (PayrollRun $record, array $data) {
                        $record->update([
                            'status'  => 'paid',
                            'paid_at' => $data['paid_at'],
                        ]);
                        $record->paySlips()->update([
                            'payment_status'    => 'paid',
                            'payment_mode'      => $data['payment_mode'],
                            'payment_reference' => $data['payment_reference'] ?? null,
                            'paid_at'           => $data['paid_at'],
                        ]);
                        Notification::make()
                            ->title('Payroll marked as paid')
                            ->success()
                            ->send();
                    }),

                // IMPORT BANK STATEMENT
                Action::make('import_bank_statement')
                    ->label('Import bank statement')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->color('gray')
                    ->visible(fn (PayrollRun $record) => $record->isApproved())
                    ->form([
                        FileUpload::make('file')
                            ->label('Bank confirmation file')
                            ->acceptedFileTypes([
                                'application/vnd.ms-excel',
                                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                'text/csv',
                            ])
                            ->required()
                            ->helperText('Upload the bank confirmation Excel/CSV after transfer'),
                    ])
                    ->action(function (PayrollRun $record, array $data) {
                        $import = new BankStatementImport($record);
                        Excel::import($import, $data['file']);
                        $results = $import->results;
                        Notification::make()
                            ->title('Bank statement processed')
                            ->body('Matched: ' . $results['matched'] . ' | Unmatched: ' . $results['unmatched'])
                            ->success()
                            ->send();
                        if (count($results['mismatched']) > 0) {
                            Notification::make()
                                ->title(count($results['mismatched']) . ' amount mismatches found')
                                ->body('Please review and mark manually')
                                ->warning()
                                ->persistent()
                                ->send();
                        }
                    }),

                // DOWNLOADS GROUP
                ActionGroup::make(  [

                    Action::make('download_payment_advice')
                        ->label('Payment advice')
                        ->icon('heroicon-o-banknotes')
                        ->color('success')
                        ->form([
                            Select::make('bank_format')
                                ->label('Select your bank format')
                                ->options([
                                    'standard' => 'Standard (works with most banks)',
                                    'hdfc'     => 'HDFC NetBanking',
                                    'icici'    => 'ICICI CorpBanking',
                                    'sbi'      => 'SBI YONO Business',
                                ])
                                ->default('standard')
                                ->required(),
                        ])
                        ->action(function (PayrollRun $record, array $data) {
                            $missing = $record->paySlips()
                                ->whereHas('employee', fn ($q) =>
                                    $q->whereNull('bank_account')
                                      ->orWhereNull('ifsc_code')
                                )
                                ->count();
                            if ($missing > 0) {
                                Notification::make()
                                    ->title($missing . ' employees excluded')
                                    ->body('Missing bank account or IFSC details')
                                    ->warning()
                                    ->send();
                            }
                            return Excel::download(
                                new PaymentAdviceExport($record, $data['bank_format']),
                                'Payment_Advice_' . str_replace(' ', '_', $record->period_label) . '.xlsx'
                            );
                        }),

                    Action::make('download_summary')
                        ->label('Payroll summary')
                        ->icon('heroicon-o-document-chart-bar')
                        ->color('info')
                        ->action(function (PayrollRun $record) {
                            return Excel::download(
                                new PayrollSummaryExport($record),
                                'Payroll_Summary_' . str_replace(' ', '_', $record->period_label) . '.xlsx'
                            );
                        }),

                    Action::make('download_complete')
                        ->label('Complete report (both sheets)')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('gray')
                        ->form([
                            Select::make('bank_format')
                                ->label('Bank format')
                                ->options([
                                    'standard' => 'Standard',
                                    'hdfc'     => 'HDFC',
                                    'icici'    => 'ICICI',
                                    'sbi'      => 'SBI',
                                ])
                                ->default('standard'),
                        ])
                        ->action(function (PayrollRun $record, array $data) {
                            return Excel::download(
                                new PayrollCompleteExport($record, $data['bank_format']),
                                'Payroll_Complete_' . str_replace(' ', '_', $record->period_label) . '.xlsx'
                            );
                        }),

                ])
                ->label('Downloads')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->visible(fn (PayrollRun $record) =>
                    in_array($record->status, ['approved', 'paid'])
                ),

                EditAction::make(),
                ViewAction::make(),
            ]);
    }
}
    