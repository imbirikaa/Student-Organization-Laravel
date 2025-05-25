<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemLog extends Model
{
    use HasFactory;
    protected $fillable = ['user_id', 'entity', 'entity_id', 'action', 'description'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
