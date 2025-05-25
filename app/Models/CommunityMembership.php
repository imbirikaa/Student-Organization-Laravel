<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommunityMembership extends Model
{
    use HasFactory;
    protected $fillable = [
        'community_id',
        'user_id',
        'community_role_id',
        'status',
        'application_date',
        'approval_date'
    ];

    public function community()
    {
        return $this->belongsTo(Community::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function role()
    {
        return $this->belongsTo(CommunityRole::class, 'community_role_id');
    }
}
