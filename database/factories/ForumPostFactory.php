<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\ForumTopic;
use App\Models\User;

class ForumPostFactory extends Factory
{
    public function definition()
    {
        return [
            'forum_topic_id' => ForumTopic::inRandomOrder()->first()?->id ?? ForumTopic::factory(),
            'user_id'        => User::inRandomOrder()->first()?->id ?? User::factory(),
            'parent_post_id' => null,
            'content'        => $this->faker->paragraph,
        ];
    }
}
