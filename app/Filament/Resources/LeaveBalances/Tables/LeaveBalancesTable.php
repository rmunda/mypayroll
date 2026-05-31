<?php

namespace App\Filament\Resources\LeaveBalances\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Actions\EditAction;
use Filament\Actions\Action;
use Filament\Tables\Filters\SelectFilter;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use App\Models\LeaveBalance;
use App\Models\FinancialYear;
use App\Models\LeaveType;
use App\Models\Employee;
use App\Services\LeaveBalanceService;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Table;

class LeaveBalancesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('employee_id')
            ->columns([
                TextColumn::make('employee.name')
                    ->label('Employee')
                    ->searchable()
                    ->sortable()
                    ->visible(
                        fn() => auth()->user()->hasAnyRole(['admin', 'hr', 'manager'])
                    ),

                TextColumn::make('employee.employee_code')
                    ->label('ID')
                    ->fontFamily('mono')
                    ->visible(
                        fn() => auth()->user()->hasAnyRole(['admin', 'hr', 'manager'])
                    ),

                TextColumn::make('financialYear.label')
                    ->label('FY')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('leaveType.name')
                    ->label('Leave type')
                    ->badge()
                    ->color(fn($record) => $record->leaveType?->color ?? 'gray'),

                TextColumn::make('allocated')
                    ->label('Allocated')
                    ->suffix(' days')
                    ->alignCenter(),

                TextColumn::make('accrued')
                    ->label('Accrued')
                    ->suffix(' days')
                    ->alignCenter(),

                TextColumn::make('carried_forward')
                    ->label('Carried')
                    ->suffix(' days')
                    ->alignCenter(),

                TextColumn::make('used')
                    ->label('Used')
                    ->suffix(' days')
                    ->color('danger')
                    ->alignCenter(),

                TextColumn::make('pending')
                    ->label('Pending')
                    ->suffix(' days')
                    ->color('warning')
                    ->alignCenter(),

                // computed available column
                TextColumn::make('available')
                    ->label('Available')
                    ->suffix(' days')
                    ->color('success')
                    ->weight('bold')
                    ->alignCenter()
                    ->getStateUsing(fn(LeaveBalance $record) => $record->available),
            ])
            ->filters([
                SelectFilter::make('financial_year_id')
                    ->label('Financial year')
                    ->options(
                        FinancialYear::orderByDesc('start_date')
                            ->pluck('label', 'id')
                    )
                    ->default(fn() => FinancialYear::current()?->id),

                SelectFilter::make('leave_type_id')
                    ->label('Leave type')
                    ->options(
                        LeaveType::where('is_active', true)
                            ->pluck('name', 'id')
                    ),

                SelectFilter::make('employee_id')
                    ->label('Employee')
                    ->options(
                        Employee::where('status', 'active')
                            ->pluck('name', 'id')
                    )
                    ->visible(
                        fn() => auth()->user()->hasAnyRole(['admin', 'hr', 'manager'])
                    ),
            ])
            ->actions([
                // manual adjustment by hr or admin
                Action::make('adjust')
                    ->label('Adjust')
                    ->icon('heroicon-o-pencil-square')
                    ->color('warning')
                    ->visible(
                        fn() => auth()->user()->hasAnyRole(['admin', 'hr'])
                    )
                    ->form([
                        TextInput::make('days')
                            ->label('Days to adjust')
                            ->numeric()
                            ->required()
                            ->helperText('Use positive to add, negative to deduct'),

                        Textarea::make('remarks')
                            ->label('Reason for adjustment')
                            ->required()
                            ->rows(2),
                    ])
                    ->action(function (LeaveBalance $record, array $data) {
                        $days    = (float) $data['days'];
                        $before  = $record->available;

                        if ($days > 0) {
                            $record->credit('allocated', abs($days));
                        } else {
                            $record->debit('used', abs($days));
                        }

                        // log the transaction
                        \App\Models\LeaveTransaction::record(
                            balance:         $record,
                            transactionType: 'adjustment',
                            days:            abs($days),
                            balanceBefore:   $before,
                            leaveId:         null,
                            remarks:         $data['remarks']
                        );

                        Notification::make()
                            ->title('Balance adjusted successfully')
                            ->success()
                            ->send();
                    }),

                // view transactions for this balance
                Action::make('view_transactions')
                    ->label('Transactions')
                    ->icon('heroicon-o-list-bullet')
                    ->color('gray')
                    ->url(fn(LeaveBalance $record) =>
                        route('filament.admin.resources.leave-transactions.index', [
                            'tableFilters[leave_balance_id][value]' => $record->id,
                        ])
                    ),
            ])
            ->headerActions([
                // initialize balances for all employees
                Action::make('initialize_balances')
                    ->label('Initialize year balances')
                    ->icon('heroicon-o-arrow-path')
                    ->color('info')
                    ->visible(
                        fn() => auth()->user()->hasRole('admin')
                    )
                    ->requiresConfirmation()
                    ->modalHeading('Initialize leave balances')
                    ->modalDescription('This will create leave balance records for all active employees for the current financial year. Existing records will not be affected.')
                    ->action(function () {
                        $fy = FinancialYear::current();
                        if (!$fy) {
                            Notification::make()
                                ->title('No active financial year found')
                                ->danger()
                                ->send();
                            return;
                        }

                        app(LeaveBalanceService::class)->initializeForYear($fy);

                        Notification::make()
                            ->title('Leave balances initialized successfully')
                            ->success()
                            ->send();
                    }),
            ]);
    }
}
