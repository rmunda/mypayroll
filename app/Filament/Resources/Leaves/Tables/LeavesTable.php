<?php

namespace App\Filament\Resources\Leaves\Tables;

use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Table;
use App\Models\Leave;
use Filament\Notifications\Notification;

use App\Services\LeaveBalanceService;

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

                // EMPLOYEE — cancel if still in request status
                Action::make('cancel')
                    ->label('Cancel')
                    ->color('danger')
                    ->icon('heroicon-o-x-circle')
                    ->visible(fn(Leave $record) =>
                        auth()->user()->hasRole('employee')
                        && $record->status === 'request'
                    )
                    ->requiresConfirmation()
                    ->action(function (Leave $record) {
                        $record->update(['status' => 'cancelled']);
                        // Call service to update leaves
                        app(LeaveBalanceService::class)->onLeaveCancelled($record);
                    }),
                
                // ADMIN / HR / MANAGER — acknowledge
                Action::make('acknowledge')
                    ->label('Mark pending')
                    ->color('info')
                    ->icon('heroicon-o-eye')
                    ->visible(fn(Leave $record) =>
                        auth()->user()->hasAnyRole(['admin', 'hr', 'manager'])
                        && $record->status === 'request'
                    )
                    ->action(function (Leave $record) {
                        $record->update(['status' => 'pending']);
                        Notification::make()
                            ->title('Marked as under review')
                            ->info()
                            ->send();
                    }),    
                    

                // ADMIN / HR / MANAGER — approve    
                Action::make('approve')
                    ->color('success')
                    ->icon('heroicon-o-check-circle')
                    ->visible(fn(Leave $record) =>
                        auth()->user()->hasAnyRole(['admin', 'hr', 'manager'])
                        && in_array($record->status, ['request', 'pending'])
                    )
                    ->requiresConfirmation()
                    ->action(function (Leave $record) {
                        $record->update([
                            'status'      => 'approved',
                            'approved_by' => auth()->id(),
                            'approved_at' => now(),
                        ]);
                        // Call service to update leaves
                        app(LeaveBalanceService::class)->onLeaveApproved($record);
                        Notification::make()
                            ->title('Leave approved')
                            ->success()
                            ->send();
                    }),

                // ADMIN / HR / MANAGER — reject    
                Action::make('reject')
                    ->color('danger')
                    ->icon('heroicon-o-x-mark')
                    ->visible(fn(Leave $record) =>
                        auth()->user()->hasAnyRole(['admin', 'hr', 'manager'])
                        && in_array($record->status, ['request', 'pending'])
                    )
                    ->requiresConfirmation()
                    ->action(function (Leave $record) {
                        $record->update(['status' => 'rejected']);
                        // Call service to update leaves
                        app(LeaveBalanceService::class)->onLeaveCancelled($record);
                        Notification::make()
                            ->title('Leave rejected')
                            ->danger()
                            ->send();
                    }),

                EditAction::make()
                ->visible(
                        fn() => auth()->user()->hasAnyRole(['admin', 'hr'])
                    ),
            ]);
    }
}
