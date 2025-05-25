<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;

class SystemLogFactory extends Factory
{
    public function definition()
    {
        return [
            'user_id' => User::inRandomOrder()->first()?->id ?? User::factory(),
            'entity' => $this->faker->word,
            'entity_id' => $this->faker->numberBetween(1, 100),
            'action' => $this->faker->randomElement(['create', 'update', 'delete']),
            'description' => $this->faker->sentence,
        ];
    }
}
