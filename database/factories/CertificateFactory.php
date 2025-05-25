<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use App\Models\Event;

class CertificateFactory extends Factory
{
    public function definition()
    {
        return [
            'event_id' => Event::inRandomOrder()->first()?->id ?? Event::factory(),
            'user_id' => User::inRandomOrder()->first()?->id ?? User::factory(),
            'certificate' => 'certs/' . $this->faker->uuid . '.pdf',
            'issue_date' => $this->faker->dateTimeBetween('-1 year', 'now'),
        ];
    }
}
