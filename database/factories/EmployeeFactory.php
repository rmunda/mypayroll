<?php

namespace Database\Factories;

use App\Models\Department;
use App\Models\Employee;
use App\Models\PayStructure;
use Illuminate\Database\Eloquent\Factories\Factory;

class EmployeeFactory extends Factory
{
    protected $model = Employee::class;

    public function definition(): array
    {
        return [
            'employee_code'   => 'EMP-' . str_pad(fake()->unique()->numberBetween(1, 999), 3, '0', STR_PAD_LEFT),
            'name'            => fake()->name(),
            'email'           => fake()->unique()->safeEmail(),
            'phone'           => fake()->phoneNumber(),
            'department_id'   => Department::factory(),
            'pay_structure_id'=> PayStructure::factory(),
            'designation'     => fake()->jobTitle(),
            'basic_salary'    => fake()->randomElement([25000, 35000, 50000, 75000, 100000]),
            'pay_frequency'   => 'monthly',
            'status'          => 'active',
            'tax_regime'      => 'new',
            'date_of_joining' => fake()->dateTimeBetween('-3 years', 'now')->format('Y-m-d'),
        ];
    }

    public function active(): static
    {
        return $this->state(['status' => 'active']);
    }

    public function inactive(): static
    {
        return $this->state(['status' => 'inactive']);
    }

    public function withSalary(float $salary): static
    {
        return $this->state(['basic_salary' => $salary]);
    }
}
