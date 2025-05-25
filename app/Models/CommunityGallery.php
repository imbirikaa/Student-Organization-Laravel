<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Community;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CommunityGallery extends Model
{
    use HasFactory;
    protected $table = 'community_gallery';

    public function community(): BelongsTo
    {
        return $this->belongsTo(Community::class, 'community_id');
    }
}
