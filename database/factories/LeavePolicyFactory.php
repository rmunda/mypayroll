<?php

namespace Database\Factories;

use App\Models\FinancialYear;
use App\Models\LeavePolicy;
use Illuminate\Database\Eloquent\Factories\Factory;

class LeavePolicyFactory extends Factory
{
    protected $model = LeavePolicy::class;

    public function definition(): array
    {
        return [
            'financial_year_id'      => FinancialYear::factory(),
            'name'                   => 'Standard Policy',
            'is_default'             => true,
            'earned_leave_accrual'   => true,
            'earned_accrual_per_month' => 1.5,
            'accrual_frequency'      => 'monthly',
            'carry_forward_earned'   => true,
            'max_carry_forward_days' => 30,
        ];
    }
}
