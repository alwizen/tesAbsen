<?php

namespace Database\Factories;

use App\Models\Department;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class DepartmentFactory extends Factory
{
    protected $model = Department::class;

    public function definition(): array
    {
        return [
            'code' => strtoupper(Str::random(5)),     // WAJIB
            'name' => $this->faker->company,
            'description' => $this->faker->sentence,
            'is_active' => true,                      // WAJIB kalau NOT NULL
        ];
    }
}
