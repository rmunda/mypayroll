<?php

namespace Database\Factories;

use App\Models\PayStructure;
use Illuminate\Database\Eloquent\Factories\Factory;

class PayStructureFactory extends Factory
{
    protected $model = PayStructure::class;

    public function definition(): array
    {
        return [
            'name'                  => fake()->randomElement(['Standard', 'Senior', 'Executive', 'Intern']),
            'hra_percentage'        => fake()->randomElement([40, 50, 60]),
            'ta_fixed'              => fake()->randomElement([1000, 1500, 2000]),
            'special_allowance_pct' => fake()->randomElement([10, 15, 20]),
            'is_default'            => false,
        ];
    }

    public function default(): static
    {
        return $this->state(['is_default' => true]);
    }
}
