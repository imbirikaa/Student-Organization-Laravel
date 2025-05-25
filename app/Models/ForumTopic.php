<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ForumTopic extends Model
{
    use HasFactory;
    protected $fillable = [
        'forum_category_id',
        'user_id',
        'topic',
        'content',
        'pinned_priority',
        'last_bump_time',
        'is_active'
    ];

    public function category()
    {
        return $this->belongsTo(ForumCategory::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function posts()
    {
        return $this->hasMany(ForumPost::class);
    }
}
