<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'role',
        'photo',
        'active',
        'company_id',
        'default_appointment_view',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'active' => 'boolean',
    ];

    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function isBarber()
    {
        return $this->role === 'attendant';
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    public function customers()
    {
        return $this->hasMany(Customer::class, 'created_by');
    }

    public function commissions()
    {
        return $this->hasMany(Commission::class, 'user_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function pagePermissions()
    {
        return $this->hasMany(UserPagePermission::class);
    }

    public function hasPagePermission(string $routeName): bool
    {
        if ($this->isAdmin()) return true;

        return $this->pagePermissions()
            ->where(function ($q) use ($routeName) {
                $q->where('page', $routeName)
                  ->orWhere('page', $routeName . '.*');
            })
            ->exists();
    }
}
