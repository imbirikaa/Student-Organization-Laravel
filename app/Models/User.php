<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'password',
        'birth_date',
        'school',
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

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_roles');
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
        return Friendship::where(function ($q) {
            $q->where('user_id', $this->id)
                ->orWhere('friend_user_id', $this->id);
        })->where('status', 'accepted');
    }
}
