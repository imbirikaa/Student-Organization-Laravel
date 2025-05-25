<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\University;

class DepartmentFactory extends Factory
{
    public function definition()
    {
        return [
            'university_id' => University::inRandomOrder()->first()?->id ?? University::factory(),
            'department_name' => $this->faker->jobTitle,
        ];
    }
}
