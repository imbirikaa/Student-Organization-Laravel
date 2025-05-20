<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Community;

class CommunityGallery extends Model
{
    protected $table = 'community_gallery';

    public function community(): BelongsTo
    {
        return $this->belongsTo(Community::class, 'community_id');
    }
}
