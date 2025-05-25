<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;
use App\Models\ForumTopic;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TopicLike extends Model
{
    use HasFactory;
    protected $table = 'topic_likes';
    public $incrementing = false;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function topic(): BelongsTo
    {
        return $this->belongsTo(ForumTopic::class, 'topic_id', 'id');
    }
}
