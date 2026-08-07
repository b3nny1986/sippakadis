<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function show(): View
    {
        return view('auth.login');
    }

    public function authenticate(LoginRequest $request, AuditLogService $audit): RedirectResponse
    {
        $credentials = $request->validated();

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()
                ->withErrors(['email' => 'Email atau password salah.'])
                ->onlyInput('email');
        }

        $user = Auth::user();

        // User non-aktif ditolak.
        if (! $user->is_active) {
            Auth::logout();

            return back()->withErrors(['email' => 'Akun Anda telah dinonaktifkan.']);
        }

        $user->update(['last_login_at' => now()]);

        $audit->aktivitas('login', "Login: {$user->email}", $user->id);
        $audit->log('auth.login', 'User', $user->id, "Login {$user->email}");

        $request->session()->regenerate();

        $role = $user->role?->name ?? '';
        $opd = $user->opd?->nama;

        $pesan = $opd
            ? "Login berhasil sebagai {$user->name} — {$role} ({$opd})."
            : "Login berhasil sebagai {$user->name} ({$role}).";

        return redirect()->intended(route('dashboard'))->with('status', $pesan);
    }

    public function logout(): RedirectResponse
    {
        $audit = app(AuditLogService::class);
        $user = Auth::user();

        if ($user) {
            $audit->aktivitas('logout', "Logout: {$user->email}", $user->id);
        }

        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();

        return redirect()->route('home');
    }
}
