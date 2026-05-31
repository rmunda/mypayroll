<?php

namespace App\Filament\Resources\WeeklyOffRules\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Models\WeeklyOffRule;
use Filament\Tables\Table;

class WeeklyOffRulesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->sortable()
                    ->searchable(),

                // show working days as comma separated list
                TextColumn::make('working_days')
                    ->label('Working days')
                    ->getStateUsing(fn(WeeklyOffRule $record) =>
                        implode(', ', $record->getWorkingDayNames())
                    ),

                // show off days
                TextColumn::make('off_days')
                    ->label('Off days')
                    ->getStateUsing(fn(WeeklyOffRule $record) =>
                        implode(', ', $record->getOffDayNames())
                    )
                    ->color('danger'),

                // saturday configuration
                TextColumn::make('saturday_type')
                    ->label('Saturday')
                    ->badge()
                    ->formatStateUsing(fn($state) => match($state) {
                        'working'       => 'Full day',
                        'half_day'      => 'Half day',
                        'alternate_1_3' => '1st & 3rd',
                        'alternate_2_4' => '2nd & 4th',
                        'non_working'   => 'Off',
                        default         => 'Off',
                    })
                    ->color(fn($state) => match($state) {
                        'working'       => 'success',
                        'half_day'      => 'warning',
                        'alternate_1_3' => 'info',
                        'alternate_2_4' => 'info',
                        'non_working'   => 'gray',
                        default         => 'gray',
                    }),

                IconColumn::make('is_default')
                    ->label('Default')
                    ->boolean(),

                // show how many employees use this rule
                TextColumn::make('employees_count')
                    ->label('Employees')
                    ->counts('employees')
                    ->suffix(' employees'),
            ])
            ->actions([
                EditAction::make()
                    ->visible(
                        fn() => auth()->user()->hasRole('admin')
                    ),

                DeleteAction::make()
                    ->visible(fn(WeeklyOffRule $record) =>
                        auth()->user()->hasRole('admin')
                        && $record->employees_count === 0
                        && !$record->is_default
                    )
                    ->tooltip('Cannot delete if employees are assigned or if default'),
            ])
            ->defaultSort('name');
    }
}
