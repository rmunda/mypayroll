<?php

namespace App\Filament\Resources\PaySlips\Tables;

use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

use App\Mail\PaySlipMail;
use App\Models\PaySlip;
use App\Services\PdfService;

use Filament\Tables\Filters\SelectFilter;

use Filament\Notifications\Notification;

use Illuminate\Support\Facades\Mail;

class PaySlipsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')

            ->columns([

                TextColumn::make('employee.employee_code')
                    ->label('EMP ID')
                    ->fontFamily('mono'),

                TextColumn::make('employee.name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('payrollRun.period_label')
                    ->label('Period'),

                TextColumn::make('gross_earnings')
                    ->label('Gross')
                    ->money('INR'),

                TextColumn::make('total_deductions')
                    ->label('Deductions')
                    ->money('INR'),

                TextColumn::make('net_pay')
                    ->label('Net pay')
                    ->money('INR')
                    ->weight('bold'),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'approved' => 'warning',
                        'sent' => 'success',
                        'paid' => 'info',
                        default => 'gray',
                    }),
            ])

            ->filters([

                SelectFilter::make('employee')
                    ->relationship('employee', 'name'),

                SelectFilter::make('payrollRun')
                    ->relationship('payrollRun', 'period_label')
                    ->label('Period'),
            ])

            ->actions([

                ViewAction::make(),

                Action::make('download')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->label('Download PDF')

                    ->action(function (PaySlip $record) {

                        $path = app(PdfService::class)
                            ->generatePaySlip($record);

                        return response()->download(
                            storage_path("app/{$path}")
                        );
                    }),

                Action::make('send')
                    ->icon('heroicon-o-envelope')

                    ->visible(fn (PaySlip $record) =>
                        $record->status !== 'sent'
                    )

                    ->action(function (PaySlip $record) {

                        $path = app(PdfService::class)
                            ->generatePaySlip($record);

                        Mail::to($record->employee->email)
                            ->send(
                                new PaySlipMail($record, $path)
                            );

                        $record->update([
                            'status' => 'sent',
                            'sent_at' => now(),
                        ]);

                        Notification::make()
                            ->title('Pay slip sent')
                            ->success()
                            ->send();
                    }),
            ]);
    }
}
