<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\CommunityRolePermission;

class Permission extends Model
{
    protected $table = 'permissions';

    public function communityRolePermissions(): HasMany
    {
        return $this->hasMany(CommunityRolePermission::class, 'permission_id', 'id');
    }
}
