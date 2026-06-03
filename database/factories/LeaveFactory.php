<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\Leave;
use App\Models\LeaveType;
use Illuminate\Database\Eloquent\Factories\Factory;

class LeaveFactory extends Factory
{
    protected $model = Leave::class;

    public function definition(): array
    {
        $from = fake()->dateTimeBetween('now', '+30 days')->format('Y-m-d');

        return [
            'employee_id'   => Employee::factory(),
            'leave_type_id' => LeaveType::factory(),
            'from_date'     => $from,
            'to_date'       => $from,
            'days'          => 1,
            'reason'        => fake()->sentence(),
            'status'        => 'request',
            'approved_by'   => null,
            'approved_at'   => null,
        ];
    }

    public function approved(): static
    {
        return $this->state([
            'status'      => 'approved',
            'approved_at' => now(),
        ]);
    }

    public function pending(): static
    {
        return $this->state(['status' => 'pending']);
    }

    public function rejected(): static
    {
        return $this->state(['status' => 'rejected']);
    }
}
