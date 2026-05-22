<?php

namespace App\Filament\Resources\PayrollRuns;

use App\Filament\Resources\PayrollRuns\Pages\CreatePayrollRun;
use App\Filament\Resources\PayrollRuns\Pages\EditPayrollRun;
use App\Filament\Resources\PayrollRuns\Pages\ListPayrollRuns;
use App\Filament\Resources\PayrollRuns\Pages\ViewPayrollRun;

use App\Jobs\SendPaySlipsJob;

use App\Models\PayrollRun;

use App\Services\PayrollService;

use BackedEnum;

use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;

use Filament\Notifications\Notification;

use Filament\Resources\Resource;

use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

use Filament\Support\Icons\Heroicon;

use Filament\Tables\Table;

use Filament\Tables\Columns\TextColumn;

class PayrollRunResource extends Resource
{
    protected static ?string $model = PayrollRun::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Payroll';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCurrencyRupee;

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'period_label';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([

                Section::make('Pay period')
                    ->columns(3)
                    ->schema([

                        TextInput::make('period_label')
                            ->required()
                            ->placeholder('e.g. April 2026'),

                        DatePicker::make('period_start')
                            ->required(),

                        DatePicker::make('period_end')
                            ->required(),
                    ]),

                Select::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'processing' => 'Processing',
                        'approved' => 'Approved',
                        'paid' => 'Paid',
                    ])
                    ->default('draft')
                    ->disabled(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
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

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'processing' => 'warning',
                        'approved', 'paid' => 'success',
                        default => 'gray',
                    }),
            ])

            ->actions([

                Action::make('process')
                    ->label('Process payroll')
                    ->icon('heroicon-o-cog-6-tooth')
                    ->color('warning')

                    ->visible(fn (PayrollRun $record) =>
                        $record->isDraft()
                    )

                    ->requiresConfirmation()

                    ->action(function (PayrollRun $record) {

                        app(PayrollService::class)
                            ->process($record);

                        Notification::make()
                            ->title('Payroll processed!')
                            ->success()
                            ->send();
                    }),

                Action::make('approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')

                    ->visible(fn (PayrollRun $record) =>
                        $record->status === 'processing'
                    )

                    ->requiresConfirmation()

                    ->action(function (PayrollRun $record) {

                        $record->update([
                            'status' => 'approved',
                            'approved_at' => now(),
                        ]);

                        Notification::make()
                            ->title('Payroll approved')
                            ->success()
                            ->send();
                    }),

                Action::make('send_slips')
                    ->label('Send pay slips')
                    ->icon('heroicon-o-envelope')

                    ->visible(fn (PayrollRun $record) =>
                        $record->isApproved()
                    )

                    ->action(function (PayrollRun $record) {

                        SendPaySlipsJob::dispatch($record);

                        Notification::make()
                            ->title('Pay slips queued for delivery')
                            ->success()
                            ->send();
                    }),

                EditAction::make(),

                ViewAction::make(),
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
            'index' => ListPayrollRuns::route('/'),
            'create' => CreatePayrollRun::route('/create'),
            'edit' => EditPayrollRun::route('/{record}/edit'),
            'view' => ViewPayrollRun::route('/{record}'),
        ];
    }
}