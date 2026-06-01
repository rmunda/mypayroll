<?php

namespace App\Filament\Widgets;

use App\Models\Department;
use App\Models\Employee;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DepartmentStatsWidget extends StatsOverviewWidget
{
    // prevent auto-discovery on the dashboard
    protected static bool $isDiscovered = false;

    protected function getStats(): array
    {
        $totalDepartments = Department::count();
        $totalEmployees   = Employee::where('status', 'active')->count();
        $avgPerDept       = $totalDepartments > 0
                            ? round($totalEmployees / $totalDepartments, 1)
                            : 0;

        $largest = Department::withCount(['employees' => fn ($q) =>
                        $q->where('status', 'active')
                    ])
                    ->orderByDesc('employees_count')
                    ->first();

        return [
            Stat::make('Total Departments', $totalDepartments)
                ->description('All departments')
                ->icon('heroicon-o-building-office')
                ->color('info'),

            Stat::make('Active Employees', $totalEmployees)
                ->description('Across all departments')
                ->icon('heroicon-o-user-group')
                ->color('success'),

            Stat::make('Avg per Department', $avgPerDept)
                ->description('Active employees')
                ->icon('heroicon-o-chart-bar')
                ->color('warning'),

            Stat::make('Largest Department', $largest?->name ?? '—')
                ->description(($largest?->employees_count ?? 0) . ' employees')
                ->icon('heroicon-o-star')
                ->color('gray'),
        ];
    }
}
