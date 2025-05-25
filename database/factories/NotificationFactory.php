<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;

class NotificationFactory extends Factory
{
    public function definition()
    {
        return [
            'user_id' => User::inRandomOrder()->first()?->id ?? User::factory(),
            'type' => $this->faker->randomElement(['alert', 'warning', 'success']),
            'notification' => $this->faker->sentence,
            'is_read' => $this->faker->boolean,
        ];
    }
}
