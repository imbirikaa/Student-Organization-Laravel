<?php

namespace Database\Factories;

use App\Models\Department;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserFactory extends Factory
{
    public function definition()
    {
        return [
            'first_name'     => $this->faker->firstName,
            'last_name'      => $this->faker->lastName,
            'email'          => $this->faker->unique()->safeEmail,
            'phone'          => $this->faker->optional()->phoneNumber,
            'password'       => bcrypt('password'),
            'birth_date'     => $this->faker->optional()->date('Y-m-d'),
            'school'         => $this->faker->optional()->company,
            'department_id'  => Department::inRandomOrder()->first()?->id ?? Department::factory(),
            'graduate_date'  => $this->faker->optional()->date('Y-m-d'),
            'nickname'       => $this->faker->userName,
            'about'          => $this->faker->optional()->paragraph,
            'profile_picture' => $this->faker->optional()->imageUrl(200, 200, 'people'),
            'membership_date' => $this->faker->optional()->dateTimeThisYear(),
            'email_verified' => $this->faker->boolean,
            'phone_verified' => $this->faker->boolean,
            'is_active'      => $this->faker->boolean(90),
            'deleted_at'     => null,
        ];
    }



    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn(array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
