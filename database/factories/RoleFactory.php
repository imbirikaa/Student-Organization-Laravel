<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class RoleFactory extends Factory
{
    public function definition()
    {
        return [
            'role' => $this->faker->jobTitle,
            'description' => $this->faker->sentence,
        ];
    }
}