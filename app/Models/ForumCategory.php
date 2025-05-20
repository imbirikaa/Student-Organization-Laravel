<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ForumCategory extends Model
{
    protected $fillable = ['category', 'description', 'sort_order', 'banner_image', 'banner_redirect_email'];

    public function topics()
    {
        return $this->hasMany(ForumTopic::class);
    }
}
