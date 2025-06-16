<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\HasFileUploads;


class Community extends Model
{
    use HasFactory, HasFileUploads;
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

    /**
     * Get the logo URL for this community
     */
    public function getLogoUrlAttribute()
    {
        // First check if there's a logo file upload
        $logoFile = $this->fileUploads()->where('upload_type', 'logo')->latest()->first();
        if ($logoFile) {
            return $logoFile->url;
        }
        
        // Fallback to the logo field if it exists
        if ($this->logo) {
            // If it's already a full URL, return as is
            if (filter_var($this->logo, FILTER_VALIDATE_URL)) {
                return $this->logo;
            }
            
            // If it already starts with /storage, use asset() directly
            if (str_starts_with($this->logo, '/storage/')) {
                return asset(ltrim($this->logo, '/'));
            }
            
            // If it starts with storage/, add the leading slash
            if (str_starts_with($this->logo, 'storage/')) {
                return asset($this->logo);
            }
            
            // Otherwise, assume it's a relative path and prepend storage/
            return asset('storage/' . ltrim($this->logo, '/'));
        }
        
        return null;
    }
}
