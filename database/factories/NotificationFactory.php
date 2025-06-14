<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Notification>
 */
class NotificationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Get a random user to be the recipient of the notification.
        $notifiable = User::inRandomOrder()->first() ?? User::factory()->create();

        // Prepare the data for the notification as an array.
        // This simulates the data that a real notification class would provide.
        $notificationData = [
            'message' => fake()->sentence(),
            'action_url' => fake()->url(),
            'action_text' => 'View Details',
        ];

        return [
            // The 'id' for notifications is a UUID string.
            'id' => Str::uuid()->toString(),

            // The 'type' column stores the full class name of a Notification class.
            // For testing, we can use a generic placeholder or a real class if you have one.
            'type' => 'App\Notifications\GeneralNotification',

            // 'notifiable_type' and 'notifiable_id' define who receives the notification.
            'notifiable_type' => get_class($notifiable),
            'notifiable_id' => $notifiable->id,

            // The 'data' column must store a JSON encoded string.
            'data' => json_encode($notificationData),

            // 'read_at' is either NULL (for unread) or a timestamp (for read).
            'read_at' => fake()->optional(0.5, null)->dateTime(), // 50% chance of being read
        ];
    }
}
