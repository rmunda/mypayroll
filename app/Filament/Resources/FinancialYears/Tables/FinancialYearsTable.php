<?php

namespace App\Filament\Resources\FinancialYears\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;

use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

use Filament\Notifications\Notification;

class FinancialYearsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('label')
                    ->searchable(),
                TextColumn::make('start_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('end_date')
                    ->date()
                    ->sortable(),
                IconColumn::make('is_current')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])

            ->filters([
                //
            ])
            
            ->recordActions([
                EditAction::make(),

                // DeleteAction::make()
                // ->before(function ($record, $action) {
                //     if (! $record->canBeDeleted()) {
                //         Notification::make()
                //             ->title('Cannot delete this Financial Year')
                //             ->body('It has payroll runs or holidays linked to it.')
                //             ->danger()
                //             ->send();

                //         $action->cancel();
                //     }
                // }),

                Action::make('markAsCurrent')
                ->label('Set as Current')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn ($record) => ! $record->is_current)
                ->requiresConfirmation()
                ->action(function ($record) {
                    $record->markAsCurrent();

                    Notification::make()
                        ->title("{$record->label} is now the current financial year.")
                        ->success()
                        ->send();
                }),
            ])

            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
