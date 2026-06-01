<?php

namespace App\Filament\Widgets;

use App\Models\Employee;
use App\Models\PayrollRun;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PayrollStatsWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    public static function canView(): bool
    {
        return auth()->user()->hasAnyRole(['admin', 'hr', 'manager']);
    }

    protected function getStats(): array
    {
        // get the most recent paid or approved payroll run
        $latestRun = PayrollRun::whereIn('status', ['paid', 'approved'])
            ->latest('period_start')
            ->first();

        $totalGross      = $latestRun?->total_gross ?? 0;
        $totalNet        = $latestRun?->total_net ?? 0;
        $totalDeductions = $latestRun?->total_deductions ?? 0;
        $periodLabel     = $latestRun?->period_label ?? 'No runs yet';
        $employeeCount   = $latestRun?->paySlips()->count() ?? 0;
        $activeEmployees = Employee::where('status', 'active')->count();

        return [
            Stat::make('Last Pay Period', $periodLabel)
                ->description('Most recent payroll run')
                ->icon('heroicon-o-calendar'),

            Stat::make('Active Employees', $activeEmployees)
                ->description("{$employeeCount} processed in last run")
                ->icon('heroicon-o-users')
                ->color('info'),

            Stat::make('Total Gross', '₹' . number_format($totalGross, 2))
                ->description('Last run gross earnings')
                ->icon('heroicon-o-banknotes')
                ->color('warning'),

            Stat::make('Total Net Pay', '₹' . number_format($totalNet, 2))
                ->description('₹' . number_format($totalDeductions, 2) . ' in deductions')
                ->icon('heroicon-o-arrow-trending-up')
                ->color('success'),
        ];
    }
}
