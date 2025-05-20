<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\ChatRoom;
use App\Models\User;

class Message extends Model
{
    protected $table = 'messages';

    public function room(): BelongsTo
    {
        return $this->belongsTo(ChatRoom::class, 'room_id', 'id');
    }

    public function senderUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_user_id', 'id');
    }
}
