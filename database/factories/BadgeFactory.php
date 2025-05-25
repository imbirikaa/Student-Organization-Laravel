<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class BadgeFactory extends Factory
{
    public function definition()
    {
        return [
            'name'        => $this->faker->word,
            'description' => $this->faker->optional()->sentence,
            'icon_path'   => $this->faker->optional()->imageUrl(100, 100, 'abstract', true, 'icon'),
        ];
    }
}
