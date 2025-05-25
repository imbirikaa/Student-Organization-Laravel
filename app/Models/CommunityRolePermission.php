<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\CommunityRole;
use App\Models\Permission;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CommunityRolePermission extends Model
{
    use HasFactory;
    protected $table = 'community_role_permissions';
    public $incrementing = false;

    public function communityRole(): BelongsTo
    {
        return $this->belongsTo(CommunityRole::class, 'community_role_id', 'id');
    }

    public function permission(): BelongsTo
    {
        return $this->belongsTo(Permission::class, 'permission_id', 'id');
    }
}
