<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;

class UserCertificateFactory extends Factory
{
    public function definition()
    {
        return [
            'user_id' => User::inRandomOrder()->first()?->id ?? User::factory(),
            'certificate_title' => $this->faker->sentence,
            'certificate_path' => 'certificates/' . $this->faker->uuid . '.pdf',
            'issue_date' => $this->faker->date(),
        ];
    }
}
