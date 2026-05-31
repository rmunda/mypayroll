<?php

namespace App\Filament\Resources\LeaveTypes\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Table;

class LeaveTypesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('code')
                    ->badge()
                    ->color('gray')
                    ->fontFamily('mono'),

                TextColumn::make('max_days_per_year')
                    ->label('Days/year')
                    ->formatStateUsing(fn($state) =>
                        $state == 0 ? 'Unlimited' : $state . ' days'
                    ),

                TextColumn::make('max_days_per_request')
                    ->label('Max/request')
                    ->formatStateUsing(fn($state) =>
                        $state == 0 ? 'No limit' : $state . ' days'
                    ),

                TextColumn::make('min_notice_days')
                    ->label('Notice')
                    ->formatStateUsing(fn($state) =>
                        $state == 0 ? 'None' : $state . ' days'
                    ),

                IconColumn::make('is_paid')
                    ->label('Paid')
                    ->boolean(),

                IconColumn::make('is_accrued')
                    ->label('Accrues')
                    ->boolean(),

                IconColumn::make('requires_document')
                    ->label('Needs doc')
                    ->boolean(),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
            ])
            ->actions([
                EditAction::make()
                    ->visible(
                        fn() => auth()->user()->hasRole('admin')
                    ),
            ])
            ->defaultSort('name');
    }
}
