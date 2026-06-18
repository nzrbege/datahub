<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Middleware untuk memblokir pengguna yang sudah dinonaktifkan
 * agar tidak bisa mengakses sistem meskipun masih punya session aktif.
 */
class CheckActiveUser
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $user = Auth::user();

            if (!$user->is_active) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')
                    ->withErrors(['username' => 'Akun Anda telah dinonaktifkan. Hubungi Super Admin.']);
            }

            // Cek jika akun dikunci (terlalu banyak gagal login)
            if ($user->isLocked()) {
                Auth::logout();
                $request->session()->invalidate();

                return redirect()->route('login')
                    ->withErrors(['username' => 'Akun Anda dikunci sementara. Coba lagi nanti.']);
            }
        }

        return $next($request);
    }
}
