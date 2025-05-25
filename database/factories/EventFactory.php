<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Community;

class EventFactory extends Factory
{
    public function definition()
    {
        return [
            'community_id'               => Community::inRandomOrder()->first()?->id ?? Community::factory(),
            'event'                      => $this->faker->sentence(3),
            'cover_image'                => $this->faker->optional()->imageUrl(640, 480, 'event'),
            'description'                => $this->faker->optional()->paragraph,
            'start_datetime'             => $this->faker->dateTimeBetween('now', '+1 month'),
            'last_application_datetime'  => $this->faker->optional()->dateTimeBetween('-1 week', 'now'),
            'location'                   => $this->faker->city,
            'certificate_type'           => $this->faker->optional()->randomElement(['pdf', 'image']),
            'min_sessions_for_certificate' => $this->faker->optional()->numberBetween(1, 5),
            'verification_type'          => $this->faker->optional()->randomElement(['code', 'qr']),
        ];
    }
}
