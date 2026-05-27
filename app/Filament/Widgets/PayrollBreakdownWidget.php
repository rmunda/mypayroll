<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;

class PayrollBreakdownWidget extends ChartWidget
{
    protected ?string $heading = 'Payroll Breakdown Widget';

    public static function canView(): bool
    {
        return auth()->user()->hasAnyRole(['admin']);
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
