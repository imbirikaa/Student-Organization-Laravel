<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\ChatRoom;
use App\Models\User;

class MessageFactory extends Factory
{
    public function definition()
    {
        return [
            'room_id'       => ChatRoom::inRandomOrder()->first()?->id ?? ChatRoom::factory(),
            'sender_user_id' => User::inRandomOrder()->first()?->id ?? User::factory(),
            'message'       => $this->faker->sentence,
            'sent_at'       => $this->faker->optional()->dateTimeThisYear(),
        ];
    }
}
