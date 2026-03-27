<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Igreja;
use App\Models\Usuario;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MusicoController extends Controller
{
    public function index(): View
    {
        return view('admin.musicos.index', [
            'musicos' => Usuario::query()
                ->with('igreja')
                ->where('perfil_global', 'member')
                ->orderBy('nome')
                ->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.musicos.create', [
            'musico' => new Usuario(),
            'igrejas' => Igreja::query()->orderBy('nome')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $dados = $this->validarMusico($request);

        Usuario::create([
            'igreja_id' => $dados['igreja_id'],
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
            ->route('admin.musicos.index')
            ->with('success', 'Músico cadastrado com sucesso.');
    }

    public function edit(Usuario $musico): View
    {
        $this->garantirMusico($musico);

        return view('admin.musicos.edit', [
            'musico' => $musico,
            'igrejas' => Igreja::query()->orderBy('nome')->get(),
        ]);
    }

    public function update(Request $request, Usuario $musico): RedirectResponse
    {
        $this->garantirMusico($musico);
        $dados = $this->validarMusico($request, $musico);

        $musico->update([
            'igreja_id' => $dados['igreja_id'],
            'nome' => $dados['nome'],
            'cpf' => $dados['cpf'],
            'email' => $dados['email'],
            'telefone' => $dados['telefone'] ?? null,
            'ativo' => (bool) ($dados['ativo'] ?? false),
            'perfil_global' => 'member',
        ]);

        return redirect()
            ->route('admin.musicos.index')
            ->with('success', 'Músico atualizado com sucesso.');
    }

    public function toggle(Usuario $musico): RedirectResponse
    {
        $this->garantirMusico($musico);

        $musico->update([
            'ativo' => !$musico->ativo,
        ]);

        return redirect()
            ->route('admin.musicos.index')
            ->with('success', $musico->ativo ? 'Músico ativado com sucesso.' : 'Músico inativado com sucesso.');
    }

    public function resetPassword(Usuario $musico): RedirectResponse
    {
        $this->garantirMusico($musico);

        $musico->update([
            'password' => $this->senhaPadraoPorCpf($musico->cpf),
            'primeiro_acesso' => true,
        ]);

        return redirect()
            ->route('admin.musicos.index')
            ->with('success', 'Senha redefinida com sucesso. O usuário deverá trocar no próximo acesso.');
    }

    public function destroy(Usuario $musico): RedirectResponse
    {
        $this->garantirMusico($musico);
        $musico->delete();

        return redirect()
            ->route('admin.musicos.index')
            ->with('success', 'Músico excluído com sucesso.');
    }

    protected function validarMusico(Request $request, ?Usuario $musico = null): array
    {
        return $request->validate([
            'igreja_id' => ['required', 'exists:igrejas,id'],
            'nome' => ['required', 'string', 'max:255'],
            'cpf' => ['required', 'string', 'max:14', Rule::unique('usuarios', 'cpf')->ignore($musico?->id)],
            'email' => ['required', 'email', 'max:255', Rule::unique('usuarios', 'email')->ignore($musico?->id)],
            'telefone' => ['nullable', 'string', 'max:20'],
            'password' => [$musico ? 'nullable' : 'nullable', 'confirmed', 'min:8'],
            'ativo' => ['nullable', 'boolean'],
        ]);
    }

    protected function garantirMusico(Usuario $musico): void
    {
        abort_unless($musico->ehMembro(), 404);
    }

    protected function senhaPadraoPorCpf(string $cpf): string
    {
        return preg_replace('/\D+/', '', $cpf) ?: $cpf;
    }
}
