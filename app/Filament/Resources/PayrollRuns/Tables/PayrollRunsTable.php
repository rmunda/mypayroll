<?php

namespace App\Filament\Resources\PayrollRuns\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Actions\ViewAction;

use App\Jobs\SendPaySlipsJob;
use Filament\Notifications\Notification;

use App\Models\PayrollRun;

use App\Services\PayrollService;

class PayrollRunsTable
{
    public static function configure(Table $table): Table
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
}
