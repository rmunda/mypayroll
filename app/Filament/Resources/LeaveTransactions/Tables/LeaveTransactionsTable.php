<?php

namespace App\Filament\Resources\LeaveTransactions\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\DatePicker;
use App\Models\LeaveTransaction;
use App\Models\FinancialYear;
use App\Models\LeaveType;
use App\Models\Employee;

class LeaveTransactionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime('d M Y h:i A')
                    ->sortable(),

                TextColumn::make('employee.name')
                    ->label('Employee')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('employee.employee_code')
                    ->label('ID')
                    ->fontFamily('mono'),

                TextColumn::make('financialYear.label')
                    ->label('FY')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('leaveType.name')
                    ->label('Leave type')
                    ->badge()
                    ->color(
                        fn($record) => $record->leaveType?->color ?? 'gray'
                    ),

                TextColumn::make('transaction_type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn($state) => match($state) {
                        'allocated'    => 'Allocated',
                        'accrued'      => 'Accrued',
                        'carry_forward'=> 'Carry forward',
                        'debit'        => 'Debit',
                        'credit'       => 'Credit',
                        'adjustment'   => 'Adjustment',
                        'encashment'   => 'Encashment',
                        'lapsed'       => 'Lapsed',
                        default        => ucfirst($state),
                    })
                    ->color(fn($state) => match($state) {
                        'allocated'    => 'info',
                        'accrued'      => 'success',
                        'carry_forward'=> 'info',
                        'credit'       => 'success',
                        'debit'        => 'danger',
                        'adjustment'   => 'warning',
                        'encashment'   => 'warning',
                        'lapsed'       => 'gray',
                        default        => 'gray',
                    }),

                // show + or - prefix for days
                TextColumn::make('days')
                    ->label('Days')
                    ->formatStateUsing(fn($state, LeaveTransaction $record) =>
                        $record->isCredit()
                            ? '+' . $state
                            : '-' . $state
                    )
                    ->color(fn(LeaveTransaction $record) =>
                        $record->isCredit() ? 'success' : 'danger'
                    )
                    ->weight('bold')
                    ->alignCenter(),

                TextColumn::make('balance_before')
                    ->label('Before')
                    ->suffix(' days')
                    ->color('gray')
                    ->alignCenter(),

                TextColumn::make('balance_after')
                    ->label('After')
                    ->suffix(' days')
                    ->weight('bold')
                    ->alignCenter(),

                TextColumn::make('remarks')
                    ->label('Remarks')
                    ->limit(30)
                    ->tooltip(fn($record) => $record->remarks),

                TextColumn::make('createdBy.name')
                    ->label('Done by')
                    ->default('System'),
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

                SelectFilter::make('transaction_type')
                    ->label('Transaction type')
                    ->options([
                        'allocated'    => 'Allocated',
                        'accrued'      => 'Accrued',
                        'carry_forward'=> 'Carry forward',
                        'debit'        => 'Debit',
                        'credit'       => 'Credit',
                        'adjustment'   => 'Adjustment',
                        'encashment'   => 'Encashment',
                        'lapsed'       => 'Lapsed',
                    ]),

                SelectFilter::make('employee_id')
                    ->label('Employee')
                    ->options(
                        Employee::where('status', 'active')
                            ->pluck('name', 'id')
                    )
                    ->searchable(),

                Filter::make('date_range')
                    ->form([
                        DatePicker::make('from')
                            ->label('From date'),
                        DatePicker::make('until')
                            ->label('Until date'),
                    ])
                    ->query(fn($query, array $data) => $query
                        ->when($data['from'],
                            fn($q, $v) => $q->whereDate('created_at', '>=', $v)
                        )
                        ->when($data['until'],
                            fn($q, $v) => $q->whereDate('created_at', '<=', $v)
                        )
                    ),
            ]);
    }
    
}
