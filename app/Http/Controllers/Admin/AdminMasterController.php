<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Acorde;
use App\Models\Igreja;
use App\Models\Missa;
use App\Models\Musica;
use App\Models\Usuario;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminMasterController extends Controller
{
    public function dashboard()
    {
        $metricas = [
            'total_usuarios' => Usuario::count(),
            'total_igrejas' => Igreja::count(),
            'total_musicas' => Musica::count(),
            'total_missas' => Missa::count(),
            'admins_locais' => Usuario::where('perfil_global', 'admin_local')->count(),
            'membros' => Usuario::where('perfil_global', 'member')->count(),
        ];

        return view('admin.dashboard', [
            'metrics' => $metricas,
        ]);
    }

    public function settings(): View
    {
        return view('admin.settings.index', [
            'metricasSistema' => [
                'total_igrejas' => Igreja::count(),
                'total_musicas' => Musica::count(),
                'total_acordes' => Acorde::count(),
                'total_usuarios' => Usuario::count(),
            ],
            'adminsMaster' => Usuario::query()
                ->where('perfil_global', 'admin_master')
                ->orderBy('nome')
                ->get(),
        ]);
    }

    public function profile()
    {
        return view('admin.settings.profile', [
            'user' => Auth::user(),
        ]);
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        /** @var \App\Models\Usuario $usuario */
        $usuario = Auth::user();

        $dados = $request->validate([
            'email' => ['required', 'email', Rule::unique('usuarios', 'email')->ignore($usuario->id)],
            'telefone' => ['nullable', 'string', 'max:20'],
            'password' => ['nullable', 'confirmed', 'min:8'],
        ]);

        $usuario->email = $dados['email'];
        $usuario->telefone = $dados['telefone'] ?? null;

        if (!empty($dados['password'])) {
            $usuario->password = $dados['password'];
            $usuario->primeiro_acesso = false;
        }

        $usuario->save();

        return back()->with('success', 'Perfil atualizado com sucesso.');
    }

    public function storeAdminMaster(Request $request): RedirectResponse
    {
        $dados = $request->validate([
            'nome' => ['required', 'string', 'max:255'],
            'cpf' => ['required', 'string', 'max:14', Rule::unique('usuarios', 'cpf')],
            'email' => ['required', 'email', 'max:255', Rule::unique('usuarios', 'email')],
            'telefone' => ['nullable', 'string', 'max:20'],
            'password' => ['nullable', 'confirmed', 'min:8'],
            'ativo' => ['nullable', 'boolean'],
        ]);

        $cpfNumerico = preg_replace('/\D+/', '', $dados['cpf']) ?: $dados['cpf'];

        Usuario::create([
            'igreja_id' => null,
            'nome' => $dados['nome'],
            'cpf' => $dados['cpf'],
            'email' => $dados['email'],
            'telefone' => $dados['telefone'] ?? null,
            'password' => $dados['password'] ?: $cpfNumerico,
            'perfil_global' => 'admin_master',
            'ativo' => (bool) ($dados['ativo'] ?? true),
            'primeiro_acesso' => true,
        ]);

        return redirect()
            ->route('admin.settings')
            ->with('success', 'Novo admin master cadastrado com sucesso. A senha inicial sera a informada no formulario ou, se ficar em branco, o CPF sem pontuacao.');
    }
}
