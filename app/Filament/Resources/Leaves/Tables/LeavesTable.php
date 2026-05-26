<?php

namespace App\Filament\Resources\Leaves\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use App\Models\Leave;
use Filament\Tables\Table;

class LeavesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('employee.name')
                    ->searchable(),

                TextColumn::make('type')
                    ->badge()
                    ->color('info'),

                TextColumn::make('from_date')
                    ->date('d M Y'),

                TextColumn::make('to_date')
                    ->date('d M Y'),

                TextColumn::make('days'),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    }),
            ])

            ->actions([

                Action::make('approve')
                    ->color('success')
                    ->icon('heroicon-o-check')

                    ->visible(fn (Leave $record) =>
                        $record->status === 'pending'
                    )

                    ->action(fn (Leave $record) =>
                        $record->update([
                            'status' => 'approved',
                            'approved_by' => auth()->id(),
                            'approved_at' => now(),
                        ])
                    ),

                Action::make('reject')
                    ->color('danger')
                    ->icon('heroicon-o-x-mark')

                    ->visible(fn (Leave $record) =>
                        $record->status === 'pending'
                    )

                    ->action(fn (Leave $record) =>
                        $record->update([
                            'status' => 'rejected',
                        ])
                    ),

                EditAction::make(),
            ]);
    }
}
