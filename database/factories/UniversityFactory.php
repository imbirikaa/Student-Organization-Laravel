<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class UniversityFactory extends Factory
{
    public function definition()
    {
        return [
            'city_id' => 1, // Replace with a real city or seed cities first
            'university_name' => $this->faker->company . ' University',
        ];
    }
}
