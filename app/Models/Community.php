<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;


class Community extends Model
{
    use HasFactory;
    protected $fillable = [
        'creator_id', // <-- Add this
        'community', 'logo', 'about', 'mission', 'vision',
        'founding_year', 'achievements', 'traditional_events',
        'contact_email', 'sponsors', 'faq',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function events()
    {
        return $this->hasMany(Event::class, 'community_id', 'id');
    }

    public function memberships()
    {
        return $this->hasMany(CommunityMembership::class, 'community_id', 'id');
    }

    public function roles()
    {
        return $this->hasMany(CommunityRole::class);
    }

    public function gallery()
    {
        return $this->hasMany(CommunityGallery::class);
    }
}
