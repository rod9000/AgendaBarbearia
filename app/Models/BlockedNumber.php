<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BlockedNumber extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'phone',
        'name',
        'reason',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public static function isBlocked(string $phone, int $companyId): bool
    {
        return self::where('company_id', $companyId)
            ->where('phone', $phone)
            ->exists();
    }
}
