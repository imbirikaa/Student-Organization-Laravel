<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;
    protected $fillable = ['university_id', 'department_name'];

    public function university()
    {
        return $this->belongsTo(University::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
