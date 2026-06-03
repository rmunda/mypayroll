<?php

namespace Database\Factories;

use App\Models\Attendance;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

class AttendanceFactory extends Factory
{
    protected $model = Attendance::class;

    public function definition(): array
    {
        return [
            'employee_id'  => Employee::factory(),
            'date'         => fake()->dateTimeBetween('-30 days', 'now')->format('Y-m-d'),
            'status'       => 'present',
            'check_in'     => '09:00:00',
            'check_out'    => '18:00:00',
            'hours_worked' => 9,
            'remarks'      => null,
        ];
    }

    public function absent(): static
    {
        return $this->state(['status' => 'absent', 'check_in' => null, 'check_out' => null, 'hours_worked' => null]);
    }

    public function onLeave(): static
    {
        return $this->state(['status' => 'on_leave', 'check_in' => null, 'check_out' => null]);
    }

    public function halfDay(): static
    {
        return $this->state(['status' => 'half_day', 'hours_worked' => 4.5]);
    }
}
