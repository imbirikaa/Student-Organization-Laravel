<?php

namespace Database\Factories;

use App\Models\Community;
use Illuminate\Database\Eloquent\Factories\Factory;

class CommunityRoleFactory extends Factory
{
    public function definition()
    {
        return [
            'community_id' => Community::inRandomOrder()->first()?->id ?? Community::factory()->create()->id,
            'role' => $this->faker->jobTitle,
        ];
    }
}
