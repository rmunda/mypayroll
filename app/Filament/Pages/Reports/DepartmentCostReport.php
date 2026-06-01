<?php

namespace App\Filament\Pages\Reports;

use App\Models\FinancialYear;
use App\Models\PayrollRun;
use App\Models\PaySlip;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class DepartmentCostReport extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationGroup = 'Reports';

    protected static ?string $navigationLabel = 'Department Cost';

    protected static ?string $navigationIcon = 'lucide-building-2';

    protected static ?int $navigationSort = 6;

    protected static string $view = 'filament.pages.reports.department-cost-report';

    public static function canAccess(): bool
    {
        return auth()->user()->hasAnyRole(['admin', 'hr', 'manager']);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                PaySlip::query()
                    ->join('employees', 'pay_slips.employee_id', '=', 'employees.id')
                    ->join('departments', 'employees.department_id', '=', 'departments.id')
                    ->select([
                        'departments.id',
                        'departments.name as department_name',
                        DB::raw('COUNT(DISTINCT pay_slips.employee_id) as employee_count'),
                        DB::raw('SUM(pay_slips.basic) as total_basic'),
                        DB::raw('SUM(pay_slips.gross_earnings) as total_gross'),
                        DB::raw('SUM(pay_slips.pf_employer) as total_pf_employer'),
                        DB::raw('SUM(pay_slips.esi_employer) as total_esi_employer'),
                        DB::raw('SUM(pay_slips.total_deductions) as total_deductions'),
                        DB::raw('SUM(pay_slips.net_pay) as total_net'),
                    ])
                    ->groupBy('departments.id', 'departments.name')
            )
            ->columns([
                TextColumn::make('department_name')
                    ->label('Department')
                    ->weight('bold')
                    ->sortable(),

                TextColumn::make('employee_count')
                    ->label('Employees')
                    ->alignCenter()
                    ->badge()
                    ->color('gray'),

                TextColumn::make('total_basic')
                    ->label('Total Basic')
                    ->numeric(decimalPlaces: 2, thousandsSeparator: ',')
                    ->prefix('₹')
                    ->alignRight()
                    ->toggleable(),

                TextColumn::make('total_gross')
                    ->label('Total Gross')
                    ->numeric(decimalPlaces: 2, thousandsSeparator: ',')
                    ->prefix('₹')
                    ->weight('bold')
                    ->alignRight(),

                TextColumn::make('total_pf_employer')
                    ->label('PF (Employer)')
                    ->numeric(decimalPlaces: 2, thousandsSeparator: ',')
                    ->prefix('₹')
                    ->color('warning')
                    ->alignRight()
                    ->toggleable(),

                TextColumn::make('total_esi_employer')
                    ->label('ESI (Employer)')
                    ->numeric(decimalPlaces: 2, thousandsSeparator: ',')
                    ->prefix('₹')
                    ->color('warning')
                    ->alignRight()
                    ->toggleable(),

                TextColumn::make('total_deductions')
                    ->label('Total Deductions')
                    ->numeric(decimalPlaces: 2, thousandsSeparator: ',')
                    ->prefix('₹')
                    ->color('danger')
                    ->alignRight(),

                TextColumn::make('total_net')
                    ->label('Total Net Pay')
                    ->numeric(decimalPlaces: 2, thousandsSeparator: ',')
                    ->prefix('₹')
                    ->color('success')
                    ->weight('bold')
                    ->alignRight(),
            ])
            ->filters([
                SelectFilter::make('payroll_run_id')
                    ->label('Pay Period')
                    ->options(
                        PayrollRun::orderByDesc('period_start')
                            ->pluck('period_label', 'id')
                    )
                    ->query(fn (Builder $query, array $state) =>
                        $state['value']
                            ? $query->where('pay_slips.payroll_run_id', $state['value'])
                            : $query
                    ),

                SelectFilter::make('financial_year')
                    ->label('Financial Year')
                    ->options(
                        FinancialYear::orderByDesc('start_date')
                            ->pluck('label', 'id')
                    )
                    ->query(fn (Builder $query, array $state) =>
                        $state['value']
                            ? $query->whereExists(function ($q) use ($state) {
                                $q->from('payroll_runs')
                                  ->whereColumn('payroll_runs.id', 'pay_slips.payroll_run_id')
                                  ->where('payroll_runs.financial_year_id', $state['value']);
                            })
                            : $query
                    ),
            ])
            ->striped()
            ->defaultSort('total_gross', 'desc');
    }
}
