<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Quiz;

class QuizQuestionFactory extends Factory
{
    public function definition()
    {
        return [
            'quiz_id' => Quiz::inRandomOrder()->first()?->id ?? Quiz::factory(),
            'question' => $this->faker->sentence,
        ];
    }
}
