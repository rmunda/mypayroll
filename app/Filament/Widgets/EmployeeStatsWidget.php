<?php

namespace App\Filament\Widgets;

use App\Models\Employee;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class EmployeeStatsWidget extends StatsOverviewWidget
{
    // prevent auto-discovery on the dashboard
    protected static bool $isDiscovered = false;

    protected function getStats(): array
    {
        $active      = Employee::where('status', 'active')->count();
        $inactive    = Employee::where('status', 'inactive')->count();
        $onLeave     = Employee::where('status', 'on_leave')->count();
        $joinedMonth = Employee::whereMonth('date_of_joining', now()->month)
                        ->whereYear('date_of_joining', now()->year)
                        ->count();

        return [
            Stat::make('Active', $active)
                ->description('Currently active employees')
                ->icon('heroicon-o-user-group')
                ->color('success'),

            Stat::make('Inactive', $inactive)
                ->description('Inactive / resigned')
                ->icon('heroicon-o-user-minus')
                ->color('danger'),

            Stat::make('On Leave', $onLeave)
                ->description('Currently on leave')
                ->icon('heroicon-o-calendar')
                ->color('warning'),

            Stat::make('Joined This Month', $joinedMonth)
                ->description(now()->format('F Y'))
                ->icon('heroicon-o-user-plus')
                ->color('info'),
        ];
    }
}
