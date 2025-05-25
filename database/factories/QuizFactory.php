<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Event;

class QuizFactory extends Factory
{
    public function definition()
    {
        return [
            'event_id' => Event::inRandomOrder()->first()?->id ?? Event::factory(),
            'required_correct_answers' => $this->faker->numberBetween(1, 5),
        ];
    }
}
