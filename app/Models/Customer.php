<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'cpf',
        'phone',
        'birth_date',
        'email',
        'photo',
        'notes',
        'points',
        'total_visits',
        'created_by',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'points' => 'integer',
        'total_visits' => 'integer',
    ];

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function redemptions()
    {
        return $this->hasMany(LoyaltyRedemption::class);
    }

    public function addPoints(int $points): void
    {
        $this->increment('points', $points);
    }

    public function spendPoints(int $points): bool
    {
        if ($this->points < $points) {
            return false;
        }
        $this->decrement('points', $points);
        return true;
    }
}
