# PHP_Laravel12_TwoFactor

## Introduction

PHP_Laravel12_TwoFactor is a secure authentication system built using Laravel 12 that implements Two-Factor Authentication (2FA) using the Laragear Two-Factor package.

This project enhances application security by requiring users to verify their identity using a time-based one-time password (TOTP) generated through an authenticator application such as Google Authenticator.

Even if a user's credentials are compromised, unauthorized access is prevented because access to the system requires a second authentication factor.

This implementation follows best practices for secure authentication and demonstrates how to integrate modern 2FA mechanisms into a Laravel application using clean architecture and simple Blade-based UI without relying on external frontend frameworks.

---

## Project Overview

This project demonstrates a complete authentication system with enforced Two-Factor Authentication (2FA) in Laravel 12.

The system ensures that every login attempt requires both:
1. Valid user credentials (email & password)
2. A valid Time-Based One-Time Password (TOTP)

After successful login, the user is redirected to a 2FA verification page where they must scan a QR code using an authenticator app and enter the generated OTP.

Only after successful OTP verification is the user granted access to the protected dashboard.

###  Authentication Flow

1. User submits login credentials
2. Credentials are validated using Laravel authentication
3. If valid, user is logged out temporarily and redirected to 2FA page
4. A QR code is generated for Google Authenticator setup
5. User scans QR code and generates OTP
6. User submits OTP for verification
7. If OTP is valid, user is fully authenticated and redirected to dashboard

###  Key Features

- Secure Login using email & password
- Forced Two-Factor Authentication for every login attempt
- QR Code generation for authenticator app setup
- OTP (Time-Based One-Time Password) verification
- Session-based temporary authentication handling
- Protected dashboard access with authentication middleware
- Logout functionality with session invalidation
- Clean and responsive UI using custom CSS (no frontend frameworks)

### Tech Stack

- Laravel 12
- PHP 8.2+
- MySQL
- Laragear Two-Factor Authentication Package
- Blade Templates
- Custom CSS (No Tailwind, No Node.js required)

---

## Step 1 — Create Project

```bash
composer create-project laravel/laravel PHP_Laravel12_TwoFactor "12.*"
cd PHP_Laravel12_TwoFactor
```

## Step 2 — Configure Environment

Edit .env file:

```.env
DB_DATABASE=twofactor_db
DB_USERNAME=root
DB_PASSWORD=
```

Run migrations:

```bash
php artisan migrate
```

---

## Step 3 — Install 2FA Package

```bash
composer require laragear/two-factor
php artisan two-factor:install
php artisan migrate
```
---

## Step 4 — Update User Model

File: app/Models/User.php

```php
<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;

use Laragear\TwoFactor\Contracts\TwoFactorAuthenticatable;
use Laragear\TwoFactor\TwoFactorAuthentication as TwoFactorAuthenticationTrait;

class User extends Authenticatable implements TwoFactorAuthenticatable
{
    use HasFactory, Notifiable, TwoFactorAuthenticationTrait;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
```

---

## Step 5 — Create Controller

```bash
php artisan make:controller TwoFactorController
```

File: app/Http/Controllers/TwoFactorController.php

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TwoFactorController extends Controller
{
    /* Show 2FA Page */
    public function show(Request $request)
    {
        $user = \App\Models\User::find(session('2fa_user_id'));

        if (!$user) {
            return redirect('/login');
        }

        if (!$user->two_factor_secret) {
            $secret = $user->createTwoFactorAuth();
        } else {
            $secret = $user->twoFactorAuth;
        }

        return view('2fa', [
            'qr' => $secret->toQr(),
            'uri' => $secret->toUri(),
        ]);
    }

    /* Login */
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {

            $user = Auth::user();

            Auth::logout();

            session(['2fa_user_id' => $user->id]);

            return redirect('/2fa');
        }

        return back()->withErrors([
            'email' => 'Invalid credentials'
        ]);
    }

    /* Verify OTP */
    public function verify(Request $request)
    {
        $request->validate([
            'code' => 'required'
        ]);

        $user = \App\Models\User::find(session('2fa_user_id'));

        if ($user && $user->confirmTwoFactorAuth($request->code)) {

            Auth::login($user);

            session()->forget('2fa_user_id');

            return redirect('/dashboard');
        }

        return back()->withErrors([
            'code' => 'Invalid OTP'
        ]);
    }
}
```

---

## Step 6 — Create Login View

File: resources/views/auth/login.blade.php

```blade
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Login</title>

<style>
body {
    font-family: 'Segoe UI', sans-serif;
    background: linear-gradient(135deg, #4f46e5, #9333ea);
    height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
}

.card {
    background: white;
    padding: 40px;
    border-radius: 16px;
    width: 360px;
    box-shadow: 0 20px 50px rgba(0,0,0,0.25);
    text-align: center;
}

h2 {
    margin-bottom: 20px;
}

input {
    width: 100%;
    padding: 12px;
    margin-bottom: 15px;
    border-radius: 8px;
    border: 1px solid #ddd;
}

button {
    width: 100%;
    padding: 12px;
    background: #4f46e5;
    color: white;
    border: none;
    border-radius: 8px;
    cursor: pointer;
}

button:hover {
    background: #4338ca;
}
</style>
</head>

<body>

<div class="card">
    <h2>Login</h2>

    <form method="POST" action="/login">
        @csrf

        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Password" required>

        <button type="submit">Login</button>
    </form>
</div>

</body>
</html>
```

---

## Step 7 — Create 2FA View

File: resources/views/2fa.blade.php

```blade
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>2FA</title>

<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Segoe UI', sans-serif;
}

body {
    background: radial-gradient(circle, #0f172a, #020617);
    height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
}

.card {
    background: #1e293b;
    padding: 30px;
    border-radius: 16px;
    width: 360px;
    text-align: center;
}

h2 {
    color: #fff;
}

p {
    color: #94a3b8;
    font-size: 13px;
    margin-bottom: 15px;
}

.qr-box {
    background: white;
    padding: 20px;
    border-radius: 12px;
    display: flex;
    justify-content: center;
    margin-bottom: 20px;
    overflow: hidden;
}

.qr-box svg,
.qr-box img {
    max-width: 100%;
}

input {
    width: 100%;
    padding: 12px;
    border-radius: 8px;
    border: none;
    margin-bottom: 15px;
    text-align: center;
}

button {
    width: 100%;
    padding: 12px;
    background: #22c55e;
    border: none;
    border-radius: 8px;
    color: white;
    cursor: pointer;
}

button:hover {
    background: #16a34a;
}

.error {
    color: red;
    margin-bottom: 10px;
}
</style>
</head>

<body>

<div class="card">
    <h2>Two-Factor Authentication</h2>
    <p>Scan QR with Google Authenticator</p>

    <div class="qr-box">
        {!! $qr !!}
    </div>

    <form method="POST" action="/2fa/verify">
        @csrf

        @error('code')
            <div class="error">{{ $message }}</div>
        @enderror

        <input type="text" name="code" placeholder="Enter OTP" required>

        <button type="submit">Verify</button>
    </form>
</div>

</body>
</html>
```

---

## Step 8 — Create Dashboard View

File: resources/views/dashboard.blade.php

```blade
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Dashboard</title>

<style>
body {
    font-family: 'Segoe UI', sans-serif;
    margin: 0;
    background: #f1f5f9;
}

.navbar {
    background: linear-gradient(135deg, #4f46e5, #6366f1);
    padding: 15px 25px;
    color: white;
    display: flex;
    justify-content: space-between;
}

.container {
    padding: 50px;
    display: flex;
    justify-content: center;
}

.card {
    background: white;
    padding: 40px;
    border-radius: 16px;
    width: 500px;
    text-align: center;
}

.btn {
    padding: 10px 18px;
    background: #ef4444;
    color: white;
    border: none;
    border-radius: 8px;
    cursor: pointer;
}
</style>
</head>

<body>

<div class="navbar">
    <h3>Dashboard</h3>

    <form method="POST" action="/logout">
        @csrf
        <button class="btn">Logout</button>
    </form>
</div>

<div class="container">
    <div class="card">
        <h1>Welcome 🎉</h1>
        <p>You are logged in with 2FA</p>
    </div>
</div>

</body>
</html>
```

---

## Step 9 — Add Routes

File: routes/web.php

```php
<?php

use App\Http\Controllers\TwoFactorController;
use Illuminate\Support\Facades\Route;

/* Login */
Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::post('/login', [TwoFactorController::class, 'login']);

/* 2FA */
Route::get('/2fa', [TwoFactorController::class, 'show']);

Route::post('/2fa/verify', [TwoFactorController::class, 'verify']);

/* Dashboard */
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware('auth');

/* Logout */
Route::post('/logout', function () {
    auth()->logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();

    return redirect('/login');
})->name('logout');

/* Home */
Route::get('/', function () {
    return view('welcome');
});
```

---

## Step 10 — Create Test User

```bash
php artisan tinker
```

```php
use App\Models\User;
use Illuminate\Support\Facades\Hash;

User::create([
    'name' => 'Test User',
    'email' => 'test@test.com',
    'password' => Hash::make('password')
]);
```

---

## Step 11 — Run Server

```bash
php artisan serve
```

Open:

```bash
http://127.0.0.1:8000/login
```
---

## FINAL FLOW 

1) Go to /login

2) Enter:

```
test@test.com
password
```
3) Redirect → /2fa

4) Scan QR code in Authenticator app

5) Enter 6-digit OTP

6) Redirect → /dashboard

---

## Output

<img src="screenshots/Screenshot 2026-03-19 175007.png" width="1000">

<img src="screenshots/Screenshot 2026-03-19 175022.png" width="1000">

<img src="screenshots/Screenshot 2026-03-19 185829.png" width="1000">

---

## Project Structure

```
PHP_Laravel12_TwoFactor/
│
├── app/
│   ├── Http/
│   │   └── Controllers/
│   │       └── TwoFactorController.php
│   │
│   ├── Models/
│   │   └── User.php
│   │
│   └── Providers/
│
├── config/
│   └── two-factor.php   (auto-generated by package)
│
├── database/
│   ├── migrations/
│   │   ├── xxxx_create_users_table.php
│   │   └── xxxx_create_two_factor_authentications_table.php
│   │
│   └── seeders/
│
├── resources/
│   ├── views/
│   │   ├── auth/
│   │   │   └── login.blade.php
│   │   │
│   │   ├── 2fa.blade.php
│   │   ├── dashboard.blade.php
│   │   └── welcome.blade.php
│   │
│   └── css/
│
├── routes/
│   └── web.php
│
├── public/
│   └── index.php
│
├── storage/
│
├── bootstrap/
│
├── vendor/
│
├── .env
├── artisan
├── composer.json
├── package.json
└── README.md
```

---

Your PHP_Laravel12_TwoFactor Project is now ready!


