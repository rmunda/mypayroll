<?php

namespace App\Filament\Resources\LeavePolicies\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\Action;
use Filament\Tables\Filters\SelectFilter;
use Filament\Notifications\Notification;
use App\Models\LeavePolicy;
use App\Models\FinancialYear;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Table;

class LeavePoliciesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('financialYear.label')
                    ->label('Financial year')
                    ->badge()
                    ->color('info')
                    ->sortable(),

                TextColumn::make('policyDetails_count')
                    ->label('Leave types')
                    ->counts('policyDetails')
                    ->suffix(' types configured'),

                TextColumn::make('earned_accrual_per_month')
                    ->label('Monthly accrual')
                    ->formatStateUsing(fn($state) =>
                        $state > 0
                            ? $state . ' days/month'
                            : 'No accrual'
                    ),

                TextColumn::make('max_carry_forward_days')
                    ->label('Max carry forward')
                    ->formatStateUsing(fn($state) =>
                        $state > 0
                            ? $state . ' days'
                            : 'No carry forward'
                    ),

                IconColumn::make('earned_leave_accrual')
                    ->label('Accrual')
                    ->boolean(),

                IconColumn::make('carry_forward_earned')
                    ->label('Carry forward')
                    ->boolean(),

                IconColumn::make('is_default')
                    ->label('Default')
                    ->boolean(),
            ])
            ->filters([
                SelectFilter::make('financial_year_id')
                    ->label('Financial year')
                    ->options(
                        FinancialYear::orderByDesc('start_date')
                            ->pluck('label', 'id')
                    ),
            ])
            ->actions([
                // set as default
                Action::make('set_default')
                    ->label('Set as default')
                    ->icon('heroicon-o-star')
                    ->color('warning')
                    ->visible(fn(LeavePolicy $record) =>
                        auth()->user()->hasAnyRole(['admin', 'hr'])
                        && !$record->is_default
                    )
                    ->requiresConfirmation()
                    ->action(function (LeavePolicy $record) {
                        $record->markAsDefault();
                        Notification::make()
                            ->title($record->name . ' set as default policy')
                            ->success()
                            ->send();
                    }),

                EditAction::make()
                    ->visible(
                        fn() => auth()->user()->hasAnyRole(['admin', 'hr'])
                    ),

                DeleteAction::make()
                    ->visible(fn(LeavePolicy $record) =>
                        auth()->user()->hasRole('admin')
                        && !$record->is_default
                    )
                    ->tooltip('Cannot delete the default policy'),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
