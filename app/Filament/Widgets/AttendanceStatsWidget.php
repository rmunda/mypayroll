<?php

namespace App\Filament\Widgets;

use App\Models\Attendance;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AttendanceStatsWidget extends StatsOverviewWidget
{
    // prevent auto-discovery on the dashboard
    protected static bool $isDiscovered = false;

    protected function getStats(): array
    {
        $present  = Attendance::whereDate('date', today())->where('status', 'present')->count();
        $absent   = Attendance::whereDate('date', today())->where('status', 'absent')->count();
        $onLeave  = Attendance::whereDate('date', today())->where('status', 'on_leave')->count();
        $halfDay  = Attendance::whereDate('date', today())->where('status', 'half_day')->count();

        return [
            Stat::make('Present', $present)
                ->description('Today')
                ->icon('heroicon-o-check-circle')
                ->color('success'),

            Stat::make('Absent', $absent)
                ->description('Today')
                ->icon('heroicon-o-x-circle')
                ->color('danger'),

            Stat::make('On Leave', $onLeave)
                ->description('Today')
                ->icon('heroicon-o-calendar')
                ->color('info'),

            Stat::make('Half Day', $halfDay)
                ->description('Today')
                ->icon('heroicon-o-clock')
                ->color('warning'),
        ];
    }
}
