<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\QuizQuestion;

class QuizAnswerFactory extends Factory
{
    public function definition()
    {
        return [
            'question_id' => QuizQuestion::inRandomOrder()->first()?->id ?? QuizQuestion::factory(),
            'answer' => $this->faker->word,
            'is_correct' => $this->faker->boolean(25),
        ];
    }
}
