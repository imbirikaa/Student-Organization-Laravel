<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use App\Models\Quiz;

class QuizSubmissionFactory extends Factory
{
    public function definition()
    {
        return [
            'quiz_id' => Quiz::inRandomOrder()->first()?->id ?? Quiz::factory(),
            'user_id' => User::inRandomOrder()->first()?->id ?? User::factory(),
            'submission_datetime' => $this->faker->dateTimeThisYear(),
            'correct_count' => $this->faker->numberBetween(0, 5),
            'is_passed' => $this->faker->boolean,
        ];
    }
}
