<?php

use Illuminate\Database\Schema\Blueprint;
use Laragear\TwoFactor\Models\TwoFactorAuthentication;

return TwoFactorAuthentication::migration()->with(function (Blueprint $table) {
    $table->string('secret_code')->nullable();
    $table->integer('failed_attempts')->default(0);
    $table->timestamp('locked_until')->nullable();
});