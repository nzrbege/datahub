<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function __construct(private AuditService $audit) {}

    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        // Rate limiting: max 5 percobaan per menit per IP
        $key = 'login:' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            $this->audit->log(AuditService::ACTION_LOGIN_FAILED, null, [
                'username' => $request->username,
                'reason' => 'rate_limited',
            ]);
            throw ValidationException::withMessages([
                'username' => "Terlalu banyak percobaan. Coba lagi dalam {$seconds} detik.",
            ]);
        }

        $user = User::where('username', $request->username)->first();

        // Cek user ada dan aktif
        if (!$user || !$user->is_active) {
            RateLimiter::hit($key);
            $this->audit->log(AuditService::ACTION_LOGIN_FAILED, null, [
                'username' => $request->username,
                'reason' => 'user_not_found_or_inactive',
            ]);
            throw ValidationException::withMessages([
                'username' => 'Username atau kata sandi tidak valid.',
            ]);
        }

        // Cek akun terkunci
        if ($user->isLocked()) {
            $this->audit->log(AuditService::ACTION_LOGIN_FAILED, $user, [
                'reason' => 'account_locked',
                'locked_until' => $user->locked_until,
            ]);
            throw ValidationException::withMessages([
                'username' => 'Akun Anda dikunci sementara karena terlalu banyak percobaan login. ' .
                           'Coba lagi pada ' . $user->locked_until->format('H:i'),
            ]);
        }

        if (!Auth::attempt($request->only('username', 'password'), $request->boolean('remember'))) {
            RateLimiter::hit($key);
            $user->incrementFailedLogin();
            $this->audit->log(AuditService::ACTION_LOGIN_FAILED, $user, [
                'reason' => 'wrong_password',
                'attempts' => $user->failed_login_attempts,
            ]);
            throw ValidationException::withMessages([
                'username' => 'Username atau kata sandi tidak valid.',
            ]);
        }

        RateLimiter::clear($key);
        $user->resetFailedLogin();
        $request->session()->regenerate();

        $this->audit->log(AuditService::ACTION_LOGIN, $user);

        // Redirect berdasarkan role
        if ($user->isSuperAdmin()) {
            return redirect()->route('superadmin.dashboard');
        }
        return redirect()->route('admin.dashboard');
    }

    public function logout(Request $request)
    {
        $this->audit->log(AuditService::ACTION_LOGOUT, auth()->user());

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
