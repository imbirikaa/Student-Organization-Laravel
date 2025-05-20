<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\User;
use App\Models\UserCoupon;

class Coupon extends Model
{
    protected $table = 'coupons';

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function userCoupons(): HasMany
    {
        return $this->hasMany(UserCoupon::class, 'coupon_id', 'id');
    }
}
