<?php

namespace App\Filament\Pages\Reports;

use App\Models\Department;
use App\Models\Employee;
use App\Models\PayrollRun;
use App\Models\PaySlip;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use BackedEnum;
use Filament\Actions\Action;

class EmployeePayslipReport extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|\UnitEnum|null $navigationGroup = 'Reports';

    protected static ?string $navigationLabel = 'Employee Payslips';

    protected static string|\BackedEnum|null $navigationIcon = 'lucide-receipt-text';

    protected static ?int $navigationSort = 2;

    protected string $view = 'filament.pages.reports.employee-payslip-report';

    public static function canAccess(): bool
    {
        return auth()->user()->hasAnyRole(['admin', 'hr', 'manager']);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                PaySlip::query()
                    ->with(['employee.department', 'payrollRun'])
                    ->latest('created_at')
            )
            ->columns([
                TextColumn::make('employee.employee_code')
                    ->label('ID')
                    ->fontFamily('mono')
                    ->searchable(),

                TextColumn::make('employee.name')
                    ->label('Employee')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('employee.department.name')
                    ->label('Department')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('payrollRun.period_label')
                    ->label('Pay Period')
                    ->sortable(),

                TextColumn::make('basic')
                    ->label('Basic')
                    ->numeric(decimalPlaces: 2, thousandsSeparator: ',')
                    ->prefix('₹')
                    ->alignRight()
                    ->toggleable(),

                TextColumn::make('hra')
                    ->label('HRA')
                    ->numeric(decimalPlaces: 2, thousandsSeparator: ',')
                    ->prefix('₹')
                    ->alignRight()
                    ->toggleable(),

                TextColumn::make('transport_allowance')
                    ->label('TA')
                    ->numeric(decimalPlaces: 2, thousandsSeparator: ',')
                    ->prefix('₹')
                    ->alignRight()
                    ->toggleable(),

                TextColumn::make('special_allowance')
                    ->label('Special')
                    ->numeric(decimalPlaces: 2, thousandsSeparator: ',')
                    ->prefix('₹')
                    ->alignRight()
                    ->toggleable(),

                TextColumn::make('gross_earnings')
                    ->label('Gross')
                    ->numeric(decimalPlaces: 2, thousandsSeparator: ',')
                    ->prefix('₹')
                    ->weight('bold')
                    ->alignRight(),

                TextColumn::make('total_deductions')
                    ->label('Deductions')
                    ->numeric(decimalPlaces: 2, thousandsSeparator: ',')
                    ->prefix('₹')
                    ->color('danger')
                    ->alignRight()
                    ->toggleable(),

                TextColumn::make('net_pay')
                    ->label('Net Pay')
                    ->numeric(decimalPlaces: 2, thousandsSeparator: ',')
                    ->prefix('₹')
                    ->color('success')
                    ->weight('bold')
                    ->alignRight(),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft'     => 'gray',
                        'approved'  => 'info',
                        'paid'      => 'success',
                        default     => 'gray',
                    }),
            ])
            ->filters([
                SelectFilter::make('payroll_run_id')
                    ->label('Pay Period')
                    ->options(
                        PayrollRun::orderByDesc('period_start')
                            ->pluck('period_label', 'id')
                    ),

                SelectFilter::make('department')
                    ->label('Department')
                    ->options(
                        Department::pluck('name', 'id')
                    )
                    ->query(fn ($query, $state) =>
                        $state['value']
                            ? $query->whereHas('employee', fn ($q) =>
                                $q->where('department_id', $state['value'])
                            )
                            : $query
                    ),

                SelectFilter::make('employee_id')
                    ->label('Employee')
                    ->options(
                        Employee::where('status', 'active')
                            ->pluck('name', 'id')
                    )
                    ->searchable(),

                SelectFilter::make('status')
                    ->options([
                        'draft'    => 'Draft',
                        'approved' => 'Approved',
                        'paid'     => 'Paid',
                    ]),
            ])
            ->actions([
                Action::make('view')
                    ->label('View')
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->url(fn (PaySlip $record) =>
                        route('filament.admin.resources.pay-slips.view', $record)
                    ),
            ])
            ->striped()
            ->defaultSort('created_at', 'desc');
    }
}
