<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'phone',
        'customer_id',
        'state',
        'context',
        'last_message_at',
    ];

    protected $casts = [
        'context' => 'array',
        'last_message_at' => 'datetime',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function messages()
    {
        return $this->hasMany(BotMessage::class);
    }

    public function getCtx(string $key, mixed $default = null): mixed
    {
        return data_get($this->context, $key, $default);
    }

    public function setCtx(string $key, mixed $value): void
    {
        $context = $this->context ?? [];
        data_set($context, $key, $value);
        $this->update(['context' => $context]);
    }

    public function clearCtx(): void
    {
        $this->update(['context' => null]);
    }

    public function isExpired(int $minutes = 30): bool
    {
        if (!$this->last_message_at) {
            return true;
        }
        return $this->last_message_at->diffInMinutes(now()) >= $minutes;
    }
}
