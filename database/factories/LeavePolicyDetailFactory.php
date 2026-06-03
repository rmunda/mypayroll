<?php

namespace Database\Factories;

use App\Models\LeavePolicy;
use App\Models\LeavePolicyDetail;
use App\Models\LeaveType;
use Illuminate\Database\Eloquent\Factories\Factory;

class LeavePolicyDetailFactory extends Factory
{
    protected $model = LeavePolicyDetail::class;

    public function definition(): array
    {
        return [
            'leave_policy_id'    => LeavePolicy::factory(),
            'leave_type_id'      => LeaveType::factory(),
            'days_per_year'      => 12,
            'accrual_per_month'  => 0,
            'carry_forward'      => false,
            'max_carry_forward'  => 0,
            'allow_encashment'   => false,
            'max_encashment_days'=> 0,
        ];
    }
}
