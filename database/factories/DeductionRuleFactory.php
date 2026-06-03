<?php

namespace Database\Factories;

use App\Models\DeductionRule;
use Illuminate\Database\Eloquent\Factories\Factory;

class DeductionRuleFactory extends Factory
{
    protected $model = DeductionRule::class;

    public function definition(): array
    {
        return [
            'name'           => 'Provident Fund (Employee)',
            'type'           => 'percentage',
            'value'          => 12.0000,
            'applies_to'     => 'basic',
            'deduction_side' => 'employee',
            'is_statutory'   => true,
            'is_active'      => true,
        ];
    }

    public function pf(): static
    {
        return $this->state([
            'name'           => 'Provident Fund (Employee)',
            'type'           => 'percentage',
            'value'          => 12.0000,
            'applies_to'     => 'basic',
            'deduction_side' => 'employee',
            'is_statutory'   => true,
        ]);
    }

    public function professionalTax(): static
    {
        return $this->state([
            'name'           => 'Professional Tax',
            'type'           => 'fixed',
            'value'          => 200.0000,
            'applies_to'     => 'gross',
            'deduction_side' => 'employee',
            'is_statutory'   => true,
        ]);
    }
}
