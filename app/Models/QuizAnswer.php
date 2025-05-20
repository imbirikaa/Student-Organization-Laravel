<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\QuizQuestion;

class QuizAnswer extends Model
{
    protected $table = 'quiz_answers';

    public function question(): BelongsTo
    {
        return $this->belongsTo(QuizQuestion::class, 'question_id', 'id');
    }
}
