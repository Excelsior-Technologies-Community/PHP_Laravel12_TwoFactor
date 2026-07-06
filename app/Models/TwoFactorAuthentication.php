<?php

namespace App\Models;

use Laragear\TwoFactor\Models\TwoFactorAuthentication as BaseTwoFactorAuthentication;

class TwoFactorAuthentication extends BaseTwoFactorAuthentication
{
    protected $table = 'two_factor_authentications';

    protected $fillable = [
        'authenticatable_id',
        'authenticatable_type',
        'shared_secret',
        'secret_code',
        'recovery_codes',
        'failed_attempts',
        'locked_until',
        'label'
    ];

    protected $casts = [
        'recovery_codes' => 'array',
        'locked_until' => 'datetime'
    ];
}