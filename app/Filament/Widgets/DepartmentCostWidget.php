<?php

namespace App\Filament\Widgets;

use App\Models\PayrollRun;
use App\Models\PaySlip;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class DepartmentCostWidget extends ChartWidget
{
    protected ?string $heading = 'Department Cost — Net Pay';

    protected static ?int $sort = 4;

    public static function canView(): bool
    {
        return auth()->user()->hasAnyRole(['admin', 'hr', 'manager']);
    }

    protected function getData(): array
    {
        // get the most recent paid or approved payroll run
        $latestRun = PayrollRun::whereIn('status', ['paid', 'approved'])
            ->latest('period_start')
            ->first();

        if (!$latestRun) {
            return ['datasets' => [], 'labels' => []];
        }

        $data = PaySlip::query()
            ->where('payroll_run_id', $latestRun->id)
            ->join('employees', 'pay_slips.employee_id', '=', 'employees.id')
            ->join('departments', 'employees.department_id', '=', 'departments.id')
            ->select('departments.name', DB::raw('SUM(pay_slips.net_pay) as total_net'))
            ->groupBy('departments.id', 'departments.name')
            ->orderByDesc('total_net')
            ->get();

        $colors = [
            'rgba(59, 130, 246, 0.8)',
            'rgba(34, 197, 94, 0.8)',
            'rgba(251, 191, 36, 0.8)',
            'rgba(239, 68, 68, 0.8)',
            'rgba(168, 85, 247, 0.8)',
            'rgba(20, 184, 166, 0.8)',
            'rgba(249, 115, 22, 0.8)',
            'rgba(236, 72, 153, 0.8)',
        ];

        return [
            'datasets' => [
                [
                    'data'            => $data->pluck('total_net')->toArray(),
                    'backgroundColor' => array_slice($colors, 0, $data->count()),
                ],
            ],
            'labels' => $data->pluck('name')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
