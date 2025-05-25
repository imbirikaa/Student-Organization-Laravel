<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class PersonalAccessToken extends Model
{
    use HasFactory;
    protected $table = 'personal_access_tokens';

    public function tokenable(): MorphTo
    {
        return $this->morphTo();
    }
}
