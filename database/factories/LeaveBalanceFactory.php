<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\FinancialYear;
use App\Models\LeaveBalance;
use App\Models\LeaveType;
use Illuminate\Database\Eloquent\Factories\Factory;

class LeaveBalanceFactory extends Factory
{
    protected $model = LeaveBalance::class;

    public function definition(): array
    {
        return [
            'employee_id'       => Employee::factory(),
            'financial_year_id' => FinancialYear::factory(),
            'leave_type_id'     => LeaveType::factory(),
            'allocated'         => 12.0,
            'accrued'           => 0.0,
            'carried_forward'   => 0.0,
            'used'              => 0.0,
            'pending'           => 0.0,
            'encashed'          => 0.0,
            'lapsed'            => 0.0,
        ];
    }

    public function withUsed(float $days): static
    {
        return $this->state(['used' => $days]);
    }

    public function withPending(float $days): static
    {
        return $this->state(['pending' => $days]);
    }
}
