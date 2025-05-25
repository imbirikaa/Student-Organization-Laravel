<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\ForumCategory;
use App\Models\User;

class ForumTopicFactory extends Factory
{
    public function definition()
    {
        return [
            'forum_category_id' => ForumCategory::inRandomOrder()->first()?->id ?? ForumCategory::factory(),
            'user_id'           => User::inRandomOrder()->first()?->id ?? User::factory(),
            'topic'             => $this->faker->sentence,
            'content'           => $this->faker->optional()->paragraph,
            'pinned_priority'   => $this->faker->optional()->numberBetween(0, 2),
            'last_bump_time'    => $this->faker->optional()->dateTimeThisYear(),
            'is_active'         => $this->faker->boolean(90),
        ];
    }
}
