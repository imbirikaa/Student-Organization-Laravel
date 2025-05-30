<?php

namespace Database\Factories;

use App\Models\Department;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UserFactory extends Factory
{

    public function definition()
    {
        $firstName = $this->faker->firstName;
        $lastName = $this->faker->lastName;
        return [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email'          => $this->faker->unique()->safeEmail,
            'phone'          => $this->faker->phoneNumber,
            'password'       => bcrypt('password'),
            'birth_date'     => $this->faker->date('Y-m-d'),
            'school'         => $this->faker->company,
            'department_id'  => Department::inRandomOrder()->first()?->id ?? Department::factory(),
            'graduate_date'  => $this->faker->date('Y-m-d'),
            'nickname'       => Str::slug($this->faker->unique()->userName),
            'about'          => $this->faker->paragraph,
            'profile_picture' => 'https://ui-avatars.com/api/?name=' . urlencode("$firstName $lastName") .
                '&background=' . ltrim($this->faker->hexColor, '#') . '&color=ffffff',
            'membership_date' => $this->faker->dateTimeThisYear(),
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
