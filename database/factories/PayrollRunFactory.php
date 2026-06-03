<?php

namespace Database\Factories;

use App\Models\FinancialYear;
use App\Models\PayrollRun;
use Illuminate\Database\Eloquent\Factories\Factory;

class PayrollRunFactory extends Factory
{
    protected $model = PayrollRun::class;

    public function definition(): array
    {
        return [
            'financial_year_id' => FinancialYear::factory(),
            'period_label'      => 'May 2026',
            'period_start'      => '2026-05-01',
            'period_end'        => '2026-05-31',
            'status'            => 'draft',
            'total_gross'       => 0,
            'total_deductions'  => 0,
            'total_net'         => 0,
        ];
    }

    public function approved(): static
    {
        return $this->state(['status' => 'approved']);
    }

    public function paid(): static
    {
        return $this->state(['status' => 'paid']);
    }
}
