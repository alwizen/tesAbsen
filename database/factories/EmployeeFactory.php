<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\Department;
use Illuminate\Database\Eloquent\Factories\Factory;

class EmployeeFactory extends Factory
{
    protected $model = Employee::class;

    public function definition(): array
    {
        return [
            'department_id' => Department::factory(),
            'rfid_number' => strtoupper($this->faker->bothify('RFID###')),
            'employee_number' => $this->faker->unique()->numerify('EMP###'),
            'name' => $this->faker->name,
            'join_date' => now()->subYear(),
            'is_active' => true,
        ];
    }
}
