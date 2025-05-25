<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\EventSession;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SessionAttendance extends Model
{
    use HasFactory;
    protected $table = 'session_attendance';

    public function session(): BelongsTo
    {
        return $this->belongsTo(EventSession::class, 'session_id', 'id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
