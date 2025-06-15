<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

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
        'is_active',
        'is_admin'
    ];

    protected $hidden = ['password'];

    protected $casts = [
        'is_admin' => 'boolean',
        'is_active' => 'boolean',
        'email_verified' => 'boolean',
        'phone_verified' => 'boolean',
    ];

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
        // Check if the picture exists on the public disk
        if ($this->profile_picture && Storage::disk('public')->exists($this->profile_picture)) {
            // *** THE FIX IS HERE ***
            // Use the asset() helper to generate the correct public URL.
            // This is friendlier to static analysis tools and is standard practice.
            return asset('storage/' . $this->profile_picture);
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

    /**
     * Check if user is a website administrator
     */
    public function isWebsiteAdmin(): bool
    {
        return $this->is_admin == true || $this->is_admin == 1;
    }

    /**
     * Check if user is either a website admin or has specific community permission
     */
    public function hasGlobalPermission($permission, $communityId = null): bool
    {
        // Website admins have access to everything
        if ($this->isWebsiteAdmin()) {
            return true;
        }

        // If no community ID provided, only website admins have global permissions
        if (!$communityId) {
            return false;
        }

        // Check community-specific permissions (this would need to be implemented elsewhere)
        return false;
    }
}
