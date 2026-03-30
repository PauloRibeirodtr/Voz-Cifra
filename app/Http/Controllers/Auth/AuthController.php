<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    private const LOGIN_MAX_ATTEMPTS = 5;

    private const LOGIN_DECAY_SECONDS = 300;

    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credenciais = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $throttleKey = $this->throttleKey($request, $credenciais['email']);

        $this->ensureIsNotRateLimited($throttleKey);

        if (!Auth::attempt([
            'email' => $credenciais['email'],
            'password' => $credenciais['password'],
            'ativo' => true,
        ], $request->boolean('remember'))) {
            RateLimiter::hit($throttleKey, self::LOGIN_DECAY_SECONDS);

            if (RateLimiter::tooManyAttempts($throttleKey, self::LOGIN_MAX_ATTEMPTS)) {
                $seconds = RateLimiter::availableIn($throttleKey);

                throw ValidationException::withMessages([
                    'email' => 'Muitas tentativas de login. Tente novamente em ' . $this->formatLockoutTime($seconds) . '.',
                ]);
            }

            throw ValidationException::withMessages([
                'email' => 'Credenciais invalidas.',
            ]);
        }

        RateLimiter::clear($throttleKey);
        $request->session()->regenerate();

        /** @var \App\Models\Usuario $usuarioAutenticado */
        $usuarioAutenticado = Auth::user();

        if ($usuarioAutenticado->ehAdminMaster()) {
            if ($usuarioAutenticado->primeiro_acesso) {
                return redirect()
                    ->route('admin.profile')
                    ->with('status', 'No primeiro acesso, atualize sua senha para continuar usando o sistema com seguranca.');
            }

            return redirect()->route('admin.dashboard');
        }

        if (method_exists($usuarioAutenticado, 'ehAdminLocal') && $usuarioAutenticado->ehAdminLocal()) {
            if ($usuarioAutenticado->primeiro_acesso) {
                return redirect()
                    ->route('local-admin.profile')
                    ->with('status', 'No primeiro acesso, atualize sua senha para continuar usando o painel da igreja com seguranca.');
            }

            return redirect()->route('local-admin.dashboard');
        }

        if (method_exists($usuarioAutenticado, 'ehMembro') && $usuarioAutenticado->ehMembro()) {
            if ($usuarioAutenticado->primeiro_acesso) {
                return redirect()
                    ->route('member.profile')
                    ->with('status', 'No primeiro acesso, atualize sua senha para liberar o painel do músico com segurança.');
            }

            return redirect()->route('member.dashboard');
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->withErrors([
            'email' => 'Credenciais invalidas.',
        ]);
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    private function ensureIsNotRateLimited(string $throttleKey): void
    {
        if (!RateLimiter::tooManyAttempts($throttleKey, self::LOGIN_MAX_ATTEMPTS)) {
            return;
        }

        $seconds = RateLimiter::availableIn($throttleKey);

        throw ValidationException::withMessages([
            'email' => 'Muitas tentativas de login. Tente novamente em ' . $this->formatLockoutTime($seconds) . '.',
        ]);
    }

    private function throttleKey(Request $request, string $identifier): string
    {
        return Str::lower(trim($identifier)) . '|' . $request->ip();
    }

    private function formatLockoutTime(int $seconds): string
    {
        if ($seconds < 60) {
            return $seconds . ' segundos';
        }

        $minutes = (int) ceil($seconds / 60);

        return $minutes . ' minuto(s)';
    }
}
