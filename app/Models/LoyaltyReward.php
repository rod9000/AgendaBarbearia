<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoyaltyReward extends Model
{
    protected $fillable = [
        'name',
        'description',
        'points_required',
        'discount_percent',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
        'discount_percent' => 'decimal:2',
    ];

    public function redemptions()
    {
        return $this->hasMany(LoyaltyRedemption::class);
    }
}
