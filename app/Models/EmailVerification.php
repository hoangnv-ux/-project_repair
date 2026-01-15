<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class EmailVerification extends Model
{
    use Notifiable;

    protected $fillable = [
        'email',
        'token',
        'expiration_time',
    ];

    protected $casts = [
        'expiration_time' => 'datetime',
    ];
}
