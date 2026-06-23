<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'email',
        'phone',
        'whatsapp',
        'cnpj',
        'trial_starts_at',
        'trial_ends_at',
        'active',
    ];

    protected $casts = [
        'trial_starts_at' => 'datetime',
        'trial_ends_at' => 'date',
        'active' => 'boolean',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function isTrialExpired()
    {
        if (!$this->trial_ends_at) {
            return false;
        }
        return $this->trial_ends_at->isPast();
    }

    public function trialDaysLeft()
    {
        if (!$this->trial_ends_at) {
            return 0;
        }
        return max(0, now()->diffInDays($this->trial_ends_at, false));
    }

    public function isTrialActive()
    {
        return !$this->isTrialExpired();
    }
}
