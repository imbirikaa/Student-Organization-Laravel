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

    public function registrations()
    {
        return $this->hasMany(EventRegistration::class);
    }

    public function registeredUsers()
    {
        return $this->belongsToMany(User::class, 'event_registrations')->withPivot('registration_date', 'status')->withTimestamps();
    }

    // Accessors for consistent API
    public function getTitleAttribute()
    {
        return $this->event;
    }

    public function getStartDateAttribute()
    {
        return $this->start_datetime ? \Carbon\Carbon::parse($this->start_datetime) : null;
    }

    public function getEndDateAttribute()
    {
        // For now, assume end_date is the same as start_date if not explicitly set
        // You might want to add an end_datetime column to the events table
        return $this->start_datetime ? \Carbon\Carbon::parse($this->start_datetime) : null;
    }
}
