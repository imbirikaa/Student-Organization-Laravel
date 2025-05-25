<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Models\Community;
use App\Models\CommunityMembership;
use App\Models\CommunityRolePermission;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CommunityRole extends Model
{
    use HasFactory;
    protected $table = 'community_roles';

    public function community(): BelongsTo
    {
        return $this->belongsTo(Community::class, 'community_id', 'id');
    }

    public function communityMemberships(): HasMany
    {
        return $this->hasMany(CommunityMembership::class, 'community_role_id', 'id');
    }

    public function communityRolePermission(): HasOne
    {
        return $this->hasOne(CommunityRolePermission::class, 'community_role_id', 'id');
    }
}
