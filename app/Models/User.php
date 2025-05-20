<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    protected $fillable = [
        'first_name', 'last_name', 'email', 'phone', 'password', 'birth_date',
        'school', 'department_id', 'graduate_date', 'nickname', 'about',
        'profile_picture', 'membership_date', 'email_verified',
        'phone_verified', 'is_active'
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
        return $this->hasMany(CommunityMembership::class);
    }
}
