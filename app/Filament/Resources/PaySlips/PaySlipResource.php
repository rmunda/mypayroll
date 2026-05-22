<?php

namespace App\Filament\Resources\PaySlips;

use App\Filament\Resources\PaySlips\Pages\ListPaySlips;
use App\Filament\Resources\PaySlips\Pages\ViewPaySlip;

use App\Mail\PaySlipMail;

use App\Models\PaySlip;

use App\Services\PdfService;

use BackedEnum;

use Filament\Actions\Action;
use Filament\Actions\ViewAction;

use Filament\Infolists\Components\TextEntry;

use Filament\Resources\Resource;

use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

use Filament\Support\Icons\Heroicon;

use Filament\Tables\Table;

use Filament\Tables\Columns\TextColumn;

use Filament\Tables\Filters\SelectFilter;

use Filament\Notifications\Notification;

use Illuminate\Support\Facades\Mail;

class PaySlipResource extends Resource
{
    protected static ?string $model = PaySlip::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Payroll';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static ?int $navigationSort = 2;

    public static function table(Table $table): Table
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

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([

                Section::make('Employee')
                    ->columns(4)
                    ->schema([

                        TextEntry::make('employee.name'),

                        TextEntry::make('employee.employee_code')
                            ->label('Employee ID'),

                        TextEntry::make('employee.designation'),

                        TextEntry::make('employee.department.name')
                            ->label('Department'),
                    ]),

                Section::make('Earnings')
                    ->columns(3)
                    ->schema([

                        TextEntry::make('basic')
                            ->money('INR'),

                        TextEntry::make('hra')
                            ->label('HRA')
                            ->money('INR'),

                        TextEntry::make('transport_allowance')
                            ->money('INR'),

                        TextEntry::make('special_allowance')
                            ->money('INR'),

                        TextEntry::make('bonus')
                            ->money('INR'),

                        TextEntry::make('gross_earnings')
                            ->money('INR')
                            ->weight('bold'),
                    ]),

                Section::make('Deductions')
                    ->columns(3)
                    ->schema([

                        TextEntry::make('pf_employee')
                            ->label('PF Employee')
                            ->money('INR'),

                        TextEntry::make('esi_employee')
                            ->label('ESI Employee')
                            ->money('INR'),

                        TextEntry::make('professional_tax')
                            ->money('INR'),

                        TextEntry::make('tds')
                            ->label('TDS')
                            ->money('INR'),

                        TextEntry::make('total_deductions')
                            ->money('INR')
                            ->weight('bold'),
                    ]),

                Section::make('Net Pay')
                    ->schema([

                        TextEntry::make('net_pay')
                            ->money('INR')
                            ->weight('bold')
                            ->size('xl'),
                    ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPaySlips::route('/'),
            'view' => ViewPaySlip::route('/{record}'),
        ];
    }
}