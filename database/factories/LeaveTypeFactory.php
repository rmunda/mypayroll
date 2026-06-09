<?php

namespace Database\Factories;

use App\Models\LeaveType;
use Illuminate\Database\Eloquent\Factories\Factory;

class LeaveTypeFactory extends Factory
{
    protected $model = LeaveType::class;

    public function definition(): array
    {
        return [
            'name'                 => 'Casual Leave',
            'code'                 => 'CL',
            'color'                => 'info',
            'is_paid'              => true,
            'is_accrued'           => false,
            'requires_document'    => false,
            'max_days_per_year'    => 12,
            'max_days_per_request' => 3,
            'min_notice_days'      => 0,
            'applicable_gender'    => 'all',
            'is_active'            => true,
        ];
    }

    public function maternity(): static
    {
        return $this->state([
            'name' => 'Maternity Leave', 'code' => 'ML', 'color' => 'pink',
            'max_days_per_year' => 182, 'applicable_gender' => 'female',
        ]);
    }

    public function paternity(): static
    {
        return $this->state([
            'name' => 'Paternity Leave', 'code' => 'PL', 'color' => 'purple',
            'max_days_per_year' => 15, 'applicable_gender' => 'male',
        ]);
    }

    public function casual(): static
    {
        return $this->state(['name' => 'Casual Leave', 'code' => 'CL', 'max_days_per_year' => 12]);
    }

    public function sick(): static
    {
        return $this->state(['name' => 'Sick Leave', 'code' => 'SL', 'color' => 'warning', 'max_days_per_year' => 8]);
    }

    public function earned(): static
    {
        return $this->state(['name' => 'Earned Leave', 'code' => 'EL', 'color' => 'success', 'is_accrued' => true, 'max_days_per_year' => 18]);
    }

    public function unpaid(): static
    {
        return $this->state(['name' => 'Unpaid Leave', 'code' => 'UL', 'color' => 'gray', 'is_paid' => false]);
    }
}
