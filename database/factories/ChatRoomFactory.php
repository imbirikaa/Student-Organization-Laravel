<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ChatRoomFactory extends Factory
{
    public function definition()
    {
        return [
            'room' => $this->faker->slug,
            'type' => $this->faker->randomElement(['private', 'group', 'support']),
        ];
    }
}
