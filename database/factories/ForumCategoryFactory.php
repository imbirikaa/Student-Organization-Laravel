<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ForumCategoryFactory extends Factory
{
    public function definition()
    {
        return [
            'category'               => $this->faker->word,
            'description'            => $this->faker->optional()->sentence,
            'sort_order'             => $this->faker->optional()->numberBetween(1, 10),
            'banner_image'           => $this->faker->optional()->imageUrl(800, 200, 'banner'),
            'banner_redirect_email'  => $this->faker->optional()->companyEmail,
        ];
    }
}
