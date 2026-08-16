<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailVerification extends Model
{
          protected $fillable = [
        'email',
        'name',
        'password',
        'otp',
        'attempts',
        'expires_at',
        'last_sent_at',
        'verified_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'last_sent_at' => 'datetime',
        'verified_at' => 'datetime',
    ];
}
