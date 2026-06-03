<?php

namespace Database\Factories;

use App\Models\WeeklyOffRule;
use Illuminate\Database\Eloquent\Factories\Factory;

class WeeklyOffRuleFactory extends Factory
{
    protected $model = WeeklyOffRule::class;

    public function definition(): array
    {
        return [
            'name'          => 'Standard (Sat-Sun Off)',
            'monday'        => true,
            'tuesday'       => true,
            'wednesday'     => true,
            'thursday'      => true,
            'friday'        => true,
            'saturday'      => false,
            'sunday'        => false,
            'saturday_type' => 'non_working',
            'is_default'    => false,
        ];
    }

    public function default(): static
    {
        return $this->state(['is_default' => true]);
    }
}
