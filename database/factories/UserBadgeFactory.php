<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;

class UserBadgeFactory extends Factory
{
    public function definition()
    {
        return [
            'user_id' => User::inRandomOrder()->first()?->id ?? User::factory(),
            'badge_id' => rand(1, 5), // Replace with Badge::inRandomOrder()->first()?->id if needed
            'assigned_date' => $this->faker->date(),
        ];
    }
}
