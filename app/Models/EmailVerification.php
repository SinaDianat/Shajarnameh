<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailVerification extends Model
{
    protected $fillable = [
        'email',
        'code',
        'user_data',
        'expires_at',
        'last_sent_at',
    ];

    protected $casts = [
        'user_data' => 'array',
        'expires_at' => 'datetime',
        'last_sent_at' => 'datetime',
    ];
}