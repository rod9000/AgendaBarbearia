<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReceiveWebhook extends Model
{
    use HasFactory;

    protected $fillable = [
        'instance',
        'event',
        'sender_phone',
        'remote_jid',
        'from_me',
        'message_content',
        'payload',
    ];

    protected $casts = [
        'from_me' => 'boolean',
        'payload' => 'array',
    ];
}
