<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class University extends Model
{
    protected $fillable = ['city_id', 'university_name'];

    public function departments()
    {
        return $this->hasMany(Department::class);
    }
}
