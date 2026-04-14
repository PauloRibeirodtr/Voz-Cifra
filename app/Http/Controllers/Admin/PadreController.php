<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Igreja;
use App\Models\Usuario;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PadreController extends Controller
{
    public function index(): View
    {
        return view('admin.padres.index', [
            'padres' => Usuario::query()
                ->where('eh_padre', true)
                ->with('igreja')
                ->orderBy('nome')
                ->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.padres.create', [
            'padre' => new Usuario([
                'eh_padre' => true,
                'perfil_global' => 'usuario',
                'ativo' => true,
            ]),
            'igrejas' => Igreja::query()->orderBy('nome')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $dados = $this->validarPadre($request);

        Usuario::create([
            'igreja_id' => $dados['igreja_id'] ?? null,
            'nome' => $dados['nome'],
            'cpf' => $dados['cpf'],
            'email' => $this->gerarEmailTecnicoPadre($dados['cpf']),
            'telefone' => null,
            'password' => Str::password(24),
            'perfil_global' => 'usuario',
            'nivel_global' => 1,
            'eh_padre' => true,
            'ativo' => (bool) ($dados['ativo'] ?? true),
            'primeiro_acesso' => false,
        ]);

        return redirect()
            ->route('admin.padres.index')
            ->with('success', 'Celebrante cadastrado com sucesso.');
    }

    public function edit(Usuario $padre): View
    {
        abort_unless($padre->eh_padre, 404);

        return view('admin.padres.edit', [
            'padre' => $padre,
            'igrejas' => Igreja::query()->orderBy('nome')->get(),
        ]);
    }

    public function update(Request $request, Usuario $padre): RedirectResponse
    {
        abort_unless($padre->eh_padre, 404);
        $dados = $this->validarPadre($request, $padre);

        $padre->update([
            'igreja_id' => $dados['igreja_id'] ?? null,
            'nome' => $dados['nome'],
            'cpf' => $dados['cpf'],
            'eh_padre' => true,
            'perfil_global' => $padre->perfil_global ?: 'usuario',
            'ativo' => (bool) ($dados['ativo'] ?? false),
        ]);

        return redirect()
            ->route('admin.padres.index')
            ->with('success', 'Celebrante atualizado com sucesso.');
    }

    public function toggle(Usuario $padre): RedirectResponse
    {
        abort_unless($padre->eh_padre, 404);

        $padre->update([
            'ativo' => !$padre->ativo,
        ]);

        return redirect()
            ->route('admin.padres.index')
            ->with('success', $padre->ativo ? 'Celebrante ativado com sucesso.' : 'Celebrante inativado com sucesso.');
    }

    protected function validarPadre(Request $request, ?Usuario $padre = null): array
    {
        return $request->validate([
            'nome' => ['required', 'string', 'max:255'],
            'cpf' => ['required', 'string', 'max:14', Rule::unique('usuarios', 'cpf')->ignore($padre?->id)],
            'igreja_id' => ['nullable', 'exists:igrejas,id'],
            'ativo' => ['nullable', 'boolean'],
        ]);
    }

    protected function gerarEmailTecnicoPadre(string $cpf): string
    {
        $cpfNumerico = preg_replace('/\D+/', '', $cpf) ?? '';

        return 'celebrante.' . $cpfNumerico . '@sem-login.local';
    }
}
