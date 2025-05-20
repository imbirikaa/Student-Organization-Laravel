<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class Friendship extends Model
{
    protected $table = 'friendships';
    public $incrementing = false;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function friendUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'friend_user_id', 'id');
    }
}
