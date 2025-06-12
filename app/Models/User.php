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
}