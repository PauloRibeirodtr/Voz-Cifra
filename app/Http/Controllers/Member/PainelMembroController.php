<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Missa;
use App\Models\Usuario;
use App\Rules\StrongPassword;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PainelMembroController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'verified_custom', 'role:member']);
    }

    public function dashboard(): View
    {
        $usuario = $this->obterUsuario();
        $igreja = $usuario?->igreja;
        $hoje = CarbonImmutable::now('America/Cuiaba')->toDateString();
        $proximaMissa = $igreja
            ? Missa::query()
                ->with(['tempoLiturgico', 'missaMusicas'])
                ->where('igreja_id', $igreja->id)
                ->where(function ($query) use ($hoje) {
                    $query->where('ativo', true)
                        ->orWhereDate('data_missa', '>=', $hoje);
                })
                ->orderByRaw('case when ativo then 0 else 1 end')
                ->orderBy('data_missa')
                ->orderBy('hora_inicio')
                ->first()
            : null;

        return view('member.dashboard', compact('usuario', 'igreja', 'proximaMissa'));
    }

    public function profile(): View
    {
        $usuario = $this->obterUsuario();

        return view('member.profile', [
            'user' => $usuario,
            'igreja' => $usuario->igreja,
        ]);
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $usuario = $this->obterUsuario();
        $primeiroAcesso = (bool) ($usuario->primeiro_acesso ?? false);

        $dados = $request->validate([
            'email' => ['required', 'email', Rule::unique('usuarios', 'email')->ignore($usuario->id)],
            'telefone' => ['nullable', 'string', 'max:20'],
            'password' => [$primeiroAcesso ? 'required' : 'nullable', 'confirmed', new StrongPassword()],
        ], [
            'password.required' => 'No primeiro acesso, defina uma nova senha para liberar o painel do músico.',
            'password.confirmed' => 'A confirmação da senha não confere.',
        ]);

        $usuario->email = $dados['email'];
        $usuario->telefone = $dados['telefone'] ?? null;

        if (!empty($dados['password'])) {
            $usuario->password = $dados['password'];
            $usuario->primeiro_acesso = false;
        }

        $usuario->save();

        return back()->with('success', $primeiroAcesso
            ? 'Senha atualizada com sucesso. O acesso do músico foi liberado.'
            : 'Perfil do músico atualizado com sucesso.');
    }

    private function obterUsuario(): Usuario
    {
        /** @var \App\Models\Usuario $usuario */
        $usuario = Auth::user();

        abort_unless($usuario && $usuario->ehMembro(), 403);

        return $usuario;
    }
}
