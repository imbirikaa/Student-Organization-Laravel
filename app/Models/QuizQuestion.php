<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Quiz;
use App\Models\QuizAnswer;

class QuizQuestion extends Model
{
    protected $table = 'quiz_questions';

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class, 'quiz_id', 'id');
    }

    public function quizAnswers(): HasMany
    {
        return $this->hasMany(QuizAnswer::class, 'question_id', 'id');
    }
}
