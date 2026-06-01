<?php

namespace App\Filament\Pages\Reports;

use App\Models\Department;
use App\Models\Employee;
use App\Models\FinancialYear;
use App\Models\PayrollRun;
use App\Models\PaySlip;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use BackedEnum;
use Illuminate\Database\Eloquent\Builder;

class TaxReport extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|\UnitEnum|null $navigationGroup = 'Reports';

    protected static ?string $navigationLabel = 'Tax / TDS Report';

    protected static string|\BackedEnum|null $navigationIcon = 'lucide-landmark';

    protected static ?int $navigationSort = 5;

    protected string $view = 'filament.pages.reports.tax-report';

    public static function canAccess(): bool
    {
        return auth()->user()->hasAnyRole(['admin', 'hr']);
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

                TextColumn::make('employee.pan_number')
                    ->label('PAN')
                    ->fontFamily('mono')
                    ->placeholder('—')
                    ->searchable(),

                TextColumn::make('employee.department.name')
                    ->label('Department')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('payrollRun.period_label')
                    ->label('Pay Period')
                    ->sortable(),

                TextColumn::make('gross_earnings')
                    ->label('Gross')
                    ->numeric(decimalPlaces: 2, thousandsSeparator: ',')
                    ->prefix('₹')
                    ->alignRight(),

                TextColumn::make('pf_employee')
                    ->label('PF (Emp)')
                    ->numeric(decimalPlaces: 2, thousandsSeparator: ',')
                    ->prefix('₹')
                    ->color('warning')
                    ->alignRight(),

                TextColumn::make('pf_employer')
                    ->label('PF (Er)')
                    ->numeric(decimalPlaces: 2, thousandsSeparator: ',')
                    ->prefix('₹')
                    ->color('warning')
                    ->alignRight()
                    ->toggleable(),

                TextColumn::make('esi_employee')
                    ->label('ESI (Emp)')
                    ->numeric(decimalPlaces: 2, thousandsSeparator: ',')
                    ->prefix('₹')
                    ->color('warning')
                    ->alignRight(),

                TextColumn::make('esi_employer')
                    ->label('ESI (Er)')
                    ->numeric(decimalPlaces: 2, thousandsSeparator: ',')
                    ->prefix('₹')
                    ->color('warning')
                    ->alignRight()
                    ->toggleable(),

                TextColumn::make('professional_tax')
                    ->label('Prof. Tax')
                    ->numeric(decimalPlaces: 2, thousandsSeparator: ',')
                    ->prefix('₹')
                    ->color('warning')
                    ->alignRight(),

                TextColumn::make('tds')
                    ->label('TDS')
                    ->numeric(decimalPlaces: 2, thousandsSeparator: ',')
                    ->prefix('₹')
                    ->color('danger')
                    ->weight('bold')
                    ->alignRight(),

                TextColumn::make('total_deductions')
                    ->label('Total Deductions')
                    ->numeric(decimalPlaces: 2, thousandsSeparator: ',')
                    ->prefix('₹')
                    ->color('danger')
                    ->weight('bold')
                    ->alignRight(),
            ])
            ->filters([
                SelectFilter::make('payroll_run_id')
                    ->label('Pay Period')
                    ->options(
                        PayrollRun::orderByDesc('period_start')
                            ->pluck('period_label', 'id')
                    ),

                SelectFilter::make('financial_year')
                    ->label('Financial Year')
                    ->options(
                        FinancialYear::orderByDesc('start_date')
                            ->pluck('label', 'id')
                    )
                    ->query(fn ($query, $state) =>
                        $state['value']
                            ? $query->whereHas('payrollRun', fn ($q) =>
                                $q->whereHas('financialYear', fn ($q2) =>
                                    $q2->where('id', $state['value'])
                                )
                            )
                            : $query
                    ),

                SelectFilter::make('department')
                    ->label('Department')
                    ->options(Department::pluck('name', 'id'))
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
                        Employee::where('status', 'active')->pluck('name', 'id')
                    )
                    ->searchable(),
            ])
            ->striped()
            ->defaultSort('created_at', 'desc');
    }
}
