<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\TwoFactorAuthentication;
use App\Models\TwoFactorTrustedDevice;

class TwoFactorController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials)) {
            $user = Auth::user();

            $trustedToken = $request->cookie('two_factor_device');
            if ($trustedToken) {
                $trusted = TwoFactorTrustedDevice::where('user_id', $user->id)
                    ->where('device_token', $trustedToken)
                    ->where('expires_at', '>', now())
                    ->first();
                if ($trusted) {
                    return redirect()->route('dashboard');
                }
            }

            session(['two_factor_user_id' => $user->id]);
            Auth::logout();

            return redirect()->route('2fa.notice');
        }

        return back()->withErrors(['email' => 'Invalid credentials']);
    }

    public function showNotice()
    {
        $userId = session('two_factor_user_id');
        if (!$userId) {
            return redirect()->route('login');
        }

        $user = User::findOrFail($userId);
        $twoFactor = TwoFactorAuthentication::where('authenticatable_id', $user->id)->first();

        $qrCodeSvg = null;
        if (!$twoFactor || !$twoFactor->enabled_at) {
            if (!$twoFactor) {
                $twoFactor = TwoFactorAuthentication::create([
                    'authenticatable_id' => $user->id,
                    'authenticatable_type' => get_class($user),
                    'shared_secret' => strtoupper(Str::random(16)),
                    'label' => $user->email,
                    'digits' => 6,
                    'seconds' => 30,
                    'window' => 0,
                    'algorithm' => 'sha1'
                ]);
            }

            if (!$twoFactor->recovery_codes) {
                $codes = [];
                for ($i = 0; $i < 8; $i++) {
                    $codes[] = Str::random(10);
                }
                $twoFactor->update(['recovery_codes' => $codes]);
            }

            $secretKey = $twoFactor->shared_secret;
            $issuer = config('app.name', 'Laravel');
            $label = $user->email;
            $chal = "otpauth://totp/" . rawurlencode($issuer . ":" . $label) . "?secret=" . $secretKey . "&issuer=" . rawurlencode($issuer);
            $qrCodeSvg = 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' . urlencode($chal);
        }

        return view('vendor.two-factor.notice', compact('qrCodeSvg'));
    }

    public function verify(Request $request)
    {
        $request->validate([
            'code' => 'required',
        ]);

        $userId = session('two_factor_user_id');
        if (!$userId) {
            return redirect()->route('login');
        }

        $user = User::findOrFail($userId);
        $twoFactor = TwoFactorAuthentication::where('authenticatable_id', $user->id)->first();

        if ($twoFactor->locked_until && $twoFactor->locked_until->isFuture()) {
            return back()->withErrors(['code' => 'Account locked. Try again later.']);
        }

        $recoveryCodes = $twoFactor->recovery_codes ?? [];
        if (in_array($request->code, $recoveryCodes)) {
            $updatedCodes = array_diff($recoveryCodes, [$request->code]);
            $twoFactor->update([
                'recovery_codes' => array_values($updatedCodes),
                'failed_attempts' => 0,
                'locked_until' => null,
                'enabled_at' => $twoFactor->enabled_at ?? now()
            ]);

            Auth::login($user);
            session()->forget('two_factor_user_id');
            return redirect()->route('dashboard');
        }

        $secret = $twoFactor->shared_secret;
        $timeSlice = floor(time() / 30);
        $valid = false;

        for ($i = -1; $i <= 1; $i++) {
            $slice = $timeSlice + $i;
            if ($this->verifyGoogleCode($secret, $slice, $request->code)) {
                $valid = true;
                break;
            }
        }

        if ($valid) {
            $twoFactor->update([
                'failed_attempts' => 0,
                'locked_until' => null,
                'enabled_at' => $twoFactor->enabled_at ?? now()
            ]);

            Auth::login($user);
            session()->forget('two_factor_user_id');

            if ($request->has('remember_device')) {
                $deviceToken = Str::random(64);
                TwoFactorTrustedDevice::create([
                    'user_id' => $user->id,
                    'device_token' => $deviceToken,
                    'ip_address' => $request->ip(),
                    'expires_at' => now()->addDays(30),
                ]);
                return redirect()->route('dashboard')->cookie('two_factor_device', $deviceToken, 43200);
            }

            return redirect()->route('dashboard');
        }

        $attempts = $twoFactor->failed_attempts + 1;
        if ($attempts >= 3) {
            $twoFactor->update([
                'failed_attempts' => $attempts,
                'locked_until' => now()->addMinutes(15)
            ]);

            try {
                $ip = $request->ip();
                Mail::raw("Security Threat Detected: 3 failed 2FA verification attempts on your account from IP: {$ip}. Account locked for 15 minutes.", function ($message) use ($user) {
                    $message->to($user->email)->subject('Security Alert: Failed 2FA Boundary Exceeded');
                });
            } catch (\Exception $e) {
            }

            return back()->withErrors(['code' => 'Too many failed attempts. Account locked for 15 minutes. Alert dispatch triggered.']);
        }

        $twoFactor->update(['failed_attempts' => $attempts]);
        return back()->withErrors(['code' => 'Verification match sequence failed.']);
    }

    private function verifyGoogleCode($secret, $timeSlice, $code)
    {
        $base32chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $base32charsFlipped = array_flip(str_split($base32chars));

        $secretUpper = strtoupper($secret);
        $secretChars = str_split($secretUpper);
        $binarySecret = "";
        
        foreach ($secretChars as $c) {
            if (!isset($base32charsFlipped[$c])) continue;
            $binarySecret .= str_pad(decbin($base32charsFlipped[$c]), 5, '0', STR_PAD_LEFT);
        }
        
        $binarySecret = str_split($binarySecret, 8);
        $secretBinaryString = "";
        foreach ($binarySecret as $b) {
            if (strlen($b) === 8) {
                $secretBinaryString .= chr(bindec($b));
            }
        }

        $timeBinary = pack('N*', 0) . pack('N*', $timeSlice);
        $hmac = hash_hmac('sha1', $timeBinary, $secretBinaryString, true);
        $offset = ord($hmac[strlen($hmac) - 1]) & 0x0F;
        
        $hashpart = substr($hmac, $offset, 4);
        $value = unpack('N', $hashpart);
        $value = $value[1];
        $value = $value & 0x7FFFFFFF;
        
        $modulo = pow(10, 6);
        $generatedCode = str_pad($value % $modulo, 6, '0', STR_PAD_LEFT);

        return ($generatedCode === $code);
    }

    public function downloadRecoveryCodes()
    {
        $userId = session('two_factor_user_id') ?? Auth::id();
        if (!$userId) {
            return redirect()->route('login');
        }

        $twoFactor = TwoFactorAuthentication::where('authenticatable_id', $userId)->first();
        if (!$twoFactor || !$twoFactor->recovery_codes) {
            return back();
        }

        $content = "BACKUP RECOVERY CODES\n=====================\n\n" . implode("\n", $twoFactor->recovery_codes);
        return response($content)
            ->header('Content-Type', 'text/plain')
            ->header('Content-Disposition', 'attachment; filename="2fa-recovery-codes.txt"');
    }

    public function dashboard()
    {
        $twoFactor = TwoFactorAuthentication::where('authenticatable_id', Auth::id())->first();
        $recoveryCodes = $twoFactor->recovery_codes ?? [];
        return view('dashboard', compact('recoveryCodes'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}