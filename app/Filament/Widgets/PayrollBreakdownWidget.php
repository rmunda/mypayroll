<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;

class PayrollBreakdownWidget extends ChartWidget
{
    protected ?string $heading = 'Payroll Breakdown Widget';

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
