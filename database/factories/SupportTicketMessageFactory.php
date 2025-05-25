<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\SupportTicket;
use App\Models\User;

class SupportTicketMessageFactory extends Factory
{
    public function definition()
    {
        return [
            'ticket_id' => SupportTicket::inRandomOrder()->first()?->id ?? SupportTicket::factory(),
            'sender_user_id' => User::inRandomOrder()->first()?->id ?? User::factory(),
            'message' => $this->faker->paragraph,
        ];
    }
}
