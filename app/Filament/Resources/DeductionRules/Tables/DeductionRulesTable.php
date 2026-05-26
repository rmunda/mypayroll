<?php

namespace App\Filament\Resources\DeductionRules\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use App\Models\DeductionRule;
use Filament\Tables\Table;

class DeductionRulesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('name'),

                TextColumn::make('type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'percentage' => 'info',
                        'fixed' => 'warning',
                        default => 'gray',
                    }),

                TextColumn::make('value')
                    ->formatStateUsing(
                        fn ($state, DeductionRule $record) =>
                            $record->type === 'percentage'
                                ? $state . '%'
                                : 'INR ' . $state
                    ),

                TextColumn::make('applies_to'),

                TextColumn::make('deduction_side'),

                IconColumn::make('is_statutory')
                    ->boolean(),

                IconColumn::make('is_active')
                    ->boolean(),
            ])

            ->actions([
                EditAction::make(),
            ]);
    }
}
