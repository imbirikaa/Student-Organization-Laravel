<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserBadge extends Model
{
    use HasFactory;
    protected $table = 'user_badges';
    protected $fillable = ['user_id', 'badge_id', 'assigned_date'];
    public $timestamps = false;
}
