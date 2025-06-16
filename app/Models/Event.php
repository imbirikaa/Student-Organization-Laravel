<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasFileUploads;

class Event extends Model
{
    use HasFactory, HasFileUploads;
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

    /**
     * Get the cover image URL for this event
     */
    public function getCoverImageUrlAttribute()
    {
        // First check if there's a cover image file upload
        $coverImageFile = $this->fileUploads()->where('upload_type', 'cover_image')->latest()->first();
        if ($coverImageFile) {
            return $coverImageFile->url;
        }
        
        // Fallback to the cover_image field if it exists
        if ($this->cover_image) {
            // If it's already a full URL, return as is
            if (filter_var($this->cover_image, FILTER_VALIDATE_URL)) {
                return $this->cover_image;
            }
            
            // If it already starts with /storage, use asset() directly
            if (str_starts_with($this->cover_image, '/storage/')) {
                return asset(ltrim($this->cover_image, '/'));
            }
            
            // If it starts with storage/, add the leading slash
            if (str_starts_with($this->cover_image, 'storage/')) {
                return asset($this->cover_image);
            }
            
            // Otherwise, assume it's a relative path and prepend storage/
            return asset('storage/' . ltrim($this->cover_image, '/'));
        }
        
        return null;
    }
}
