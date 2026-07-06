<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TwoFactorTrustedDevice extends Model
{
    protected $table = 'two_factor_trusted_devices';

    protected $fillable = [
        'user_id',
        'device_token',
        'ip_address',
        'expires_at'
    ];

    protected $casts = [
        'expires_at' => 'datetime'
    ];
}