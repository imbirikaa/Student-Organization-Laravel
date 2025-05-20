<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\UserRole;

class Role extends Model
{
    protected $table = 'roles';

    public function userRoles(): HasMany
    {
        return $this->hasMany(UserRole::class, 'role_id', 'id');
    }
}
