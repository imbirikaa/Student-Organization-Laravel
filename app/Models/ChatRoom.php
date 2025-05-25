<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatRoom extends Model
{
    use HasFactory;
    protected $fillable = ['room', 'type'];

    public function users()
    {
        return $this->belongsToMany(User::class, 'chat_room_users');
    }

    public function messages()
    {
        return $this->hasMany(Message::class, 'room_id');
    }
}
