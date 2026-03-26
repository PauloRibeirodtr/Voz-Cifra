<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
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

        $usuario = Usuario::where('email', $credenciais['email'])->first();

        if (!$usuario || !($usuario->ativo ?? false)) {
            return back()->withErrors([
                'email' => 'Usuário inexistente ou inativo.',
            ])->onlyInput('email');
        }

        if (!Auth::attempt($credenciais, $request->boolean('remember'))) {
            return back()->withErrors([
                'email' => 'As credenciais informadas estão incorretas.',
            ])->onlyInput('email');
        }

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

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->withErrors([
            'email' => 'Este perfil ainda não está liberado nesta etapa do sistema.',
        ]);
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
