<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\ChatRoomUser;
use App\Models\User;

class ChatRoom extends Model
{
    protected $table = 'chat_rooms';

    public function chatRoomUser(): HasOne
    {
        return $this->hasOne(ChatRoomUser::class, 'room_id', 'id');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'messages', 'room_id', 'user_id');
    }
}
