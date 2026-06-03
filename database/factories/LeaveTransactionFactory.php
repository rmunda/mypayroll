<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\FinancialYear;
use App\Models\LeaveBalance;
use App\Models\LeaveTransaction;
use App\Models\LeaveType;
use Illuminate\Database\Eloquent\Factories\Factory;

class LeaveTransactionFactory extends Factory
{
    protected $model = LeaveTransaction::class;

    public function definition(): array
    {
        return [
            'employee_id'       => Employee::factory(),
            'financial_year_id' => FinancialYear::factory(),
            'leave_balance_id'  => LeaveBalance::factory(),
            'leave_type_id'     => LeaveType::factory(),
            'leave_id'          => null,
            'transaction_type'  => 'allocated',
            'days'              => 12.0,
            'balance_before'    => 0.0,
            'balance_after'     => 12.0,
            'remarks'           => 'Annual allocation',
            'created_by'        => null,
        ];
    }

    public function debit(): static
    {
        return $this->state(['transaction_type' => 'debit']);
    }

    public function adjustment(): static
    {
        return $this->state(['transaction_type' => 'adjustment']);
    }
}
