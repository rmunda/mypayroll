<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PayrollStatsWidget extends StatsOverviewWidget
{

    public static function canView(): bool
    {
        return auth()->user()->hasAnyRole(['admin', 'hr', 'manager']);
    }

    protected function getStats(): array
    {
        return [
            //
        ];
    }
}
