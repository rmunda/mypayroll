<?php

namespace Database\Factories;

use App\Models\FinancialYear;
use Illuminate\Database\Eloquent\Factories\Factory;

class FinancialYearFactory extends Factory
{
    protected $model = FinancialYear::class;

    public function definition(): array
    {
        return [
            'label'      => 'FY 2025-26',
            'start_date' => '2025-04-01',
            'end_date'   => '2026-03-31',
            'is_current' => false,
        ];
    }

    public function current(): static
    {
        return $this->state(['is_current' => true]);
    }
}
