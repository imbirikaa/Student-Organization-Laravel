<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use App\Traits\HasFileUploads;
use App\Traits\HasCommunityPermissions;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles, HasFileUploads, HasCommunityPermissions;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'password',
        'birth_date',
        'university_id',
        'department_id',
        'graduate_date',
        'nickname',
        'about',
        'profile_picture',
        'membership_date',
        'email_verified',
        'phone_verified',
        'is_active'
    ];

    protected $hidden = ['password'];

    protected $appends = [
        'profile_picture_url',
        'friend_count',
        'community_count',
        'event_count'
    ];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function communities()
    {
        return $this->belongsToMany(Community::class, 'community_memberships');
    }

    public function events()
    {
        return $this->belongsToMany(Event::class, 'event_user');
    }

    public function eventRegistrations()
    {
        return $this->hasMany(EventRegistration::class);
    }

    public function registeredEvents()
    {
        return $this->belongsToMany(Event::class, 'event_registrations')->withPivot('registration_date', 'status')->withTimestamps();
    }

    public function friendships()
    {
        return $this->hasMany(Friendship::class, 'user_id');
    }

    public function inverseFriendships()
    {
        return $this->hasMany(Friendship::class, 'friend_user_id');
    }

    public function allAcceptedFriendships()
    {
        return Friendship::where('status', 'accepted')->where(function ($q) {
            $q->where('user_id', $this->id)
                ->orWhere('friend_user_id', $this->id);
        });
    }

    // --- ACCESSORS ---

    /**
     * Get the full URL to the user's profile picture.
     */
    public function getProfilePictureUrlAttribute()
    {
        // First check if there's a profile picture file upload
        $profilePictureFile = $this->fileUploads()->where('upload_type', 'profile_picture')->latest()->first();
        if ($profilePictureFile) {
            return $profilePictureFile->url;
        }
        
        // Fallback to the profile_picture field if it exists
        if ($this->profile_picture) {
            // If it's already a full URL, return as is
            if (filter_var($this->profile_picture, FILTER_VALIDATE_URL)) {
                return $this->profile_picture;
            }
            
            // If it already starts with /storage, use asset() directly
            if (str_starts_with($this->profile_picture, '/storage/')) {
                return asset(ltrim($this->profile_picture, '/'));
            }
            
            // If it starts with storage/, add the leading slash
            if (str_starts_with($this->profile_picture, 'storage/')) {
                return asset($this->profile_picture);
            }
            
            // Check if the picture exists on the public disk
            if (Storage::disk('public')->exists($this->profile_picture)) {
                return asset('storage/' . $this->profile_picture);
            }
        }
        
        // Return a default placeholder image if no profile picture is set
        return 'https://via.placeholder.com/150/000000/FFFFFF/?text=User';
    }

    /**
     * Get the user's friend count.
     */
    public function getFriendCountAttribute()
    {
        return $this->allAcceptedFriendships()->count();
    }

    /**
     * Get the user's community count.
     */
    public function getCommunityCountAttribute()
    {
        return $this->communities()->count();
    }

    /**
     * Get the user's event count.
     */
    public function getEventCountAttribute()
    {
        return $this->events()->count();
    }
}
