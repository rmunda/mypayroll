<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;

class DepartmentCostWidget extends ChartWidget
{
    protected ?string $heading = 'Department Cost Widget';

    public static function canView(): bool
    {
        return auth()->user()->hasAnyRole(['admin', 'hr', 'manager']);
    }

    protected function getData(): array
    {
        return [
            //
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
