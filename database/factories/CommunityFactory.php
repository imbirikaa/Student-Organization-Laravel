<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class CommunityFactory extends Factory
{
    public function definition()
    {
        return [
            'community'          => $this->faker->company,
            'logo'               => $this->faker->optional()->imageUrl(200, 200, 'business'),
            'about'              => $this->faker->optional()->paragraph,
            'mission'            => $this->faker->optional()->sentence,
            'vision'             => $this->faker->optional()->sentence,
            'founding_year'      => $this->faker->optional()->year,
            'achievements'       => $this->faker->optional()->paragraph,
            'traditional_events' => $this->faker->optional()->sentence,
            'contact_email'      => $this->faker->optional()->companyEmail,
            'sponsors'           => $this->faker->optional()->sentence,
            'faq'                => $this->faker->optional()->paragraph,
        ];
    }
}
