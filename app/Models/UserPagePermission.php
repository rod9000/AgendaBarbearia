<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserPagePermission extends Model
{
    protected $fillable = ['user_id', 'page'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
