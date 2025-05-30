<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;
    protected $fillable = [
        'community_id',
        'event',
        'cover_image',
        'description',
        'start_datetime',
        'last_application_datetime',
        'location',
        'certificate_type',
        'min_sessions_for_certificate',
        'verification_type'
    ];

    public function community()
    {
        return $this->belongsTo(Community::class);
    }

    public function sessions()
    {
        return $this->hasMany(EventSession::class);
    }

    public function certificates()
    {
        return $this->hasMany(Certificate::class);
    }
    public function users()
    {
        return $this->belongsToMany(User::class, 'event_user')->withTimestamps();
    }
}
