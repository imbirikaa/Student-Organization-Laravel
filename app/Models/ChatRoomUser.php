<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\ChatRoom;
use App\Models\User;

class ChatRoomUser extends Model
{
    protected $table = 'chat_room_users';

    public function room(): BelongsTo
    {
        return $this->belongsTo(ChatRoom::class, 'room_id', 'id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
