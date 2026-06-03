<?php

namespace Database\Factories;

use App\Models\FinancialYear;
use App\Models\Holiday;
use Illuminate\Database\Eloquent\Factories\Factory;

class HolidayFactory extends Factory
{
    protected $model = Holiday::class;

    public function definition(): array
    {
        return [
            'financial_year_id' => FinancialYear::factory(),
            'name'              => fake()->randomElement(['Diwali', 'Holi', 'Independence Day', 'Republic Day', 'Christmas']),
            'date'              => fake()->dateTimeBetween('2025-04-01', '2026-03-31')->format('Y-m-d'),
            'type'              => 'national',
            'is_paid'           => true,
        ];
    }
}
