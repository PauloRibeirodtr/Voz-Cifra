<?php

namespace App\Http\Controllers\LocalAdmin;

use App\Http\Controllers\Controller;
use App\Models\Igreja;
use App\Models\Usuario;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MusicoController extends Controller
{
    public function index(): View
    {
        $igreja = $this->obterIgreja();

        return view('local-admin.musicos.index', [
            'igreja' => $igreja,
            'musicos' => Usuario::query()
                ->where('perfil_global', 'member')
                ->where('igreja_id', $igreja->id)
                ->orderBy('nome')
                ->get(),
        ]);
    }

    public function create(): View
    {
        return view('local-admin.musicos.create', [
            'igreja' => $this->obterIgreja(),
            'musico' => new Usuario(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $dados = $this->validarMusico($request);
        $igreja = $this->obterIgreja();

        Usuario::create([
            'igreja_id' => $igreja->id,
            'nome' => $dados['nome'],
            'cpf' => $dados['cpf'],
            'email' => $dados['email'],
            'telefone' => $dados['telefone'] ?? null,
            'password' => $dados['password'] ?: $this->senhaPadraoPorCpf($dados['cpf']),
            'perfil_global' => 'member',
            'ativo' => (bool) ($dados['ativo'] ?? true),
            'primeiro_acesso' => true,
        ]);

        return redirect()
            ->route('local-admin.musicos.index')
            ->with('success', 'Músico cadastrado com sucesso.');
    }

    public function edit(Usuario $musico): View
    {
        $this->garantirMusicoDaIgreja($musico);

        return view('local-admin.musicos.edit', [
            'igreja' => $this->obterIgreja(),
            'musico' => $musico,
        ]);
    }

    public function update(Request $request, Usuario $musico): RedirectResponse
    {
        $this->garantirMusicoDaIgreja($musico);
        $dados = $this->validarMusico($request, $musico);

        $musico->update([
            'nome' => $dados['nome'],
            'cpf' => $dados['cpf'],
            'email' => $dados['email'],
            'telefone' => $dados['telefone'] ?? null,
            'ativo' => (bool) ($dados['ativo'] ?? false),
            'perfil_global' => 'member',
            'igreja_id' => $this->obterIgreja()->id,
        ]);

        return redirect()
            ->route('local-admin.musicos.index')
            ->with('success', 'Músico atualizado com sucesso.');
    }

    public function toggle(Usuario $musico): RedirectResponse
    {
        $this->garantirMusicoDaIgreja($musico);

        $musico->update([
            'ativo' => !$musico->ativo,
        ]);

        return redirect()
            ->route('local-admin.musicos.index')
            ->with('success', $musico->ativo ? 'Músico ativado com sucesso.' : 'Músico inativado com sucesso.');
    }

    public function resetPassword(Usuario $musico): RedirectResponse
    {
        $this->garantirMusicoDaIgreja($musico);

        $musico->update([
            'password' => $this->senhaPadraoPorCpf($musico->cpf),
            'primeiro_acesso' => true,
        ]);

        return redirect()
            ->route('local-admin.musicos.index')
            ->with('success', 'Senha redefinida com sucesso. O usuário deverá trocar no próximo acesso.');
    }

    public function destroy(Usuario $musico): RedirectResponse
    {
        $this->garantirMusicoDaIgreja($musico);
        $musico->delete();

        return redirect()
            ->route('local-admin.musicos.index')
            ->with('success', 'Músico excluído com sucesso.');
    }

    protected function validarMusico(Request $request, ?Usuario $musico = null): array
    {
        return $request->validate([
            'nome' => ['required', 'string', 'max:255'],
            'cpf' => ['required', 'string', 'max:14', Rule::unique('usuarios', 'cpf')->ignore($musico?->id)],
            'email' => ['required', 'email', 'max:255', Rule::unique('usuarios', 'email')->ignore($musico?->id)],
            'telefone' => ['nullable', 'string', 'max:20'],
            'password' => [$musico ? 'nullable' : 'nullable', 'confirmed', 'min:8'],
            'ativo' => ['nullable', 'boolean'],
        ]);
    }

    protected function obterUsuario(): Usuario
    {
        /** @var \App\Models\Usuario $usuario */
        $usuario = Auth::user();

        abort_unless($usuario && $usuario->ehAdminLocal(), 403);

        return $usuario;
    }

    protected function obterIgreja(): Igreja
    {
        $igreja = $this->obterUsuario()->igreja;

        abort_unless($igreja !== null, 404);

        return $igreja;
    }

    protected function garantirMusicoDaIgreja(Usuario $musico): void
    {
        abort_unless($musico->ehMembro() && (int) $musico->igreja_id === (int) $this->obterIgreja()->id, 404);
    }

    protected function senhaPadraoPorCpf(string $cpf): string
    {
        return preg_replace('/\D+/', '', $cpf) ?: $cpf;
    }
}
