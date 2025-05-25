<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserCertificate extends Model
{
    use HasFactory;
    protected $fillable = ['user_id', 'certificate_title', 'certificate_path', 'issue_date'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
