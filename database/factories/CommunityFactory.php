<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CommunityFactory extends Factory
{
    public function definition()
    {
        $community = $this->faker->unique()->company;
        return [
            'community'          => $community,
            'logo'               => 'https://ui-avatars.com/api/?name=' . urlencode("$community") .
                '&background=' . ltrim($this->faker->hexColor, '#') . '&color=ffffff',
            'about'              => $this->faker->paragraph,
            'mission'            => $this->faker->sentence,
            'vision'             => $this->faker->sentence,
            'founding_year'      => $this->faker->year,
            'achievements'       => $this->faker->paragraph,
            'traditional_events' => $this->faker->sentence,
            'contact_email'      => $this->faker->companyEmail,
            'sponsors'           => $this->faker->sentence,
            'faq'                => $this->faker->paragraph,
            'creator_id'         => User::factory()
        ];
    }
}
