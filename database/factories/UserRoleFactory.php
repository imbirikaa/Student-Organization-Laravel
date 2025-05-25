<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;

class UserRoleFactory extends Factory
{
    public function definition()
    {
        return [
            'user_id' => User::inRandomOrder()->first()?->id ?? User::factory(),
            'role_id' => rand(1, 5), // Replace with actual logic if roles exist
            'assigned_date' => now(),
        ];
    }
}
