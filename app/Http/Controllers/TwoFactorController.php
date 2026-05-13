<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class TwoFactorController extends Controller
{
    /* Show 2FA Page */
    public function show(Request $request)
    {
        $user = User::find(session('2fa_user_id'));

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

        $user = User::find(session('2fa_user_id'));

        if ($user && $user->confirmTwoFactorAuth($request->code)) {

            Auth::login($user);

            session()->forget('2fa_user_id');

            return redirect('/dashboard');
        }

        return back()->withErrors([
            'code' => 'Invalid OTP'
        ]);
    }

    /* Dashboard */
    public function dashboard(Request $request)
    {
        $search = $request->search;

        $users = User::when($search, function ($query) use ($search) {
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%");
        })->oldest()->paginate(5);

        $totalUsers = User::count();

        return view('dashboard', compact(
            'users',
            'search',
            'totalUsers'
        ));
    }

    /* Delete User */
    public function destroy(User $user)
    {
        $user->delete();

        return back()->with('success', 'User deleted successfully');
    }
}