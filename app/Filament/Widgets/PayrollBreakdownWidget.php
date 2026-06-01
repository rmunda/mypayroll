<?php

namespace App\Filament\Widgets;

use App\Models\PayrollRun;
use Filament\Widgets\ChartWidget;

class PayrollBreakdownWidget extends ChartWidget
{
    protected ?string $heading = 'Payroll Breakdown — Gross vs Net';

    protected static ?int $sort = 3;

    public static function canView(): bool
    {
        return auth()->user()->hasAnyRole(['admin', 'hr', 'manager']);
    }

    protected function getData(): array
    {
        $runs = PayrollRun::whereIn('status', ['paid', 'approved'])
            ->orderBy('period_start')
            ->take(12)
            ->get();

        return [
            'datasets' => [
                [
                    'label'           => 'Gross Earnings',
                    'data'            => $runs->pluck('total_gross')->toArray(),
                    'backgroundColor' => 'rgba(251, 191, 36, 0.7)',
                    'borderColor'     => 'rgba(251, 191, 36, 1)',
                    'borderWidth'     => 2,
                ],
                [
                    'label'           => 'Net Pay',
                    'data'            => $runs->pluck('total_net')->toArray(),
                    'backgroundColor' => 'rgba(34, 197, 94, 0.7)',
                    'borderColor'     => 'rgba(34, 197, 94, 1)',
                    'borderWidth'     => 2,
                ],
                [
                    'label'           => 'Total Deductions',
                    'data'            => $runs->pluck('total_deductions')->toArray(),
                    'backgroundColor' => 'rgba(239, 68, 68, 0.7)',
                    'borderColor'     => 'rgba(239, 68, 68, 1)',
                    'borderWidth'     => 2,
                ],
            ],
            'labels' => $runs->pluck('period_label')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
