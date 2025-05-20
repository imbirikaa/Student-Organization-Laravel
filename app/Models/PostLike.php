<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\ForumPost;
use App\Models\User;

class PostLike extends Model
{
    protected $table = 'post_likes';
    public $incrementing = false;

    public function post(): BelongsTo
    {
        return $this->belongsTo(ForumPost::class, 'post_id', 'id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
