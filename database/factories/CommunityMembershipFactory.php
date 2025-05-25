<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Community;
use App\Models\User;
use App\Models\CommunityRole;

class CommunityMembershipFactory extends Factory
{
    public function definition()
    {
        return [
            'community_id'       => Community::inRandomOrder()->first()?->id ?? Community::factory(),
            'user_id'            => User::inRandomOrder()->first()?->id ?? User::factory(),
            'community_role_id'  => CommunityRole::inRandomOrder()->first()?->id ?? CommunityRole::factory(),
            'status'             => $this->faker->randomElement(['pending', 'approved', 'rejected']),
            'application_date'   => $this->faker->optional()->dateTimeThisYear(),
            'approval_date'      => $this->faker->optional()->dateTimeThisYear(),
        ];
    }
}
