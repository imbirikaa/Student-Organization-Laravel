<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Community extends Model
{
    use HasFactory;
    protected $fillable = [
        'community', 'logo', 'about', 'mission', 'vision', 'founding_year',
        'achievements', 'traditional_events', 'contact_email', 'sponsors', 'faq'
    ];

    public function events()
    {
        return $this->hasMany(Event::class);
    }

    public function members()
    {
        return $this->hasMany(CommunityMembership::class);
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
