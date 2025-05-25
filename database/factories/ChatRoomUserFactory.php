<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\ChatRoom;
use App\Models\ChatRoomUser;
use App\Models\User;

class ChatRoomUserFactory extends Factory
{
    public function definition()
    {
        $chatRoomId = ChatRoom::inRandomOrder()->first()?->id ?? ChatRoom::factory()->create()->id;
        $userId = User::inRandomOrder()->first()?->id ?? User::factory()->create()->id;

        // Ensure no duplicate pairing
        while (ChatRoomUser::where('chat_room_id', $chatRoomId)
                ->where('user_id', $userId)->exists()) {
            $chatRoomId = ChatRoom::inRandomOrder()->first()?->id ?? ChatRoom::factory()->create()->id;
            $userId = User::inRandomOrder()->first()?->id ?? User::factory()->create()->id;
        }

        return [
            'chat_room_id' => $chatRoomId,
            'user_id' => $userId,
            'role' => $this->faker->randomElement(['admin', 'member']),
        ];
    }
}
