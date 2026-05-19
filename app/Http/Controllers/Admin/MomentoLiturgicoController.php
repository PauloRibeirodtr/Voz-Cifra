<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MomentoLiturgico;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MomentoLiturgicoController extends Controller
{
    public function index(): View
    {
        return view('admin.momentos-liturgicos.index', [
            'momentosLiturgicos' => MomentoLiturgico::orderByRaw('ordem_exibicao asc nulls last')->orderBy('nome')->get(),
            'routePrefix' => $this->routePrefix(),
        ]);
    }

    public function create(): View
    {
        return view('admin.momentos-liturgicos.create', [
            'routePrefix' => $this->routePrefix(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $dados = $request->validate([
            'nome' => ['required', 'string', 'max:255', Rule::unique('classificacoes_liturgicas', 'nome')->where(fn ($query) => $query->where('tipo', 'momento'))],
            'descricao' => ['nullable', 'string'],
            'ordem_exibicao' => ['nullable', 'integer', 'min:1'],
            'ativo' => ['nullable', 'boolean'],
        ]);

        MomentoLiturgico::create([
            'nome' => $dados['nome'],
            'descricao' => $dados['descricao'] ?? null,
            'ordem_exibicao' => $dados['ordem_exibicao'] ?? null,
            'ativo' => (bool) ($dados['ativo'] ?? true),
        ]);

        return redirect()
            ->route($this->routeName('momentos-liturgicos.index'))
            ->with('success', 'Momento liturgico cadastrado com sucesso.');
    }

    public function edit(MomentoLiturgico $momentoLiturgico): View
    {
        return view('admin.momentos-liturgicos.edit', [
            'momentoLiturgico' => $momentoLiturgico,
            'routePrefix' => $this->routePrefix(),
        ]);
    }

    public function update(Request $request, MomentoLiturgico $momentoLiturgico): RedirectResponse
    {
        $dados = $request->validate([
            'nome' => ['required', 'string', 'max:255', Rule::unique('classificacoes_liturgicas', 'nome')->where(fn ($query) => $query->where('tipo', 'momento'))->ignore($momentoLiturgico->id)],
            'descricao' => ['nullable', 'string'],
            'ordem_exibicao' => ['nullable', 'integer', 'min:1'],
            'ativo' => ['nullable', 'boolean'],
        ]);

        $momentoLiturgico->update([
            'nome' => $dados['nome'],
            'descricao' => $dados['descricao'] ?? null,
            'ordem_exibicao' => $dados['ordem_exibicao'] ?? null,
            'ativo' => (bool) ($dados['ativo'] ?? false),
        ]);

        return redirect()
            ->route($this->routeName('momentos-liturgicos.index'))
            ->with('success', 'Momento liturgico atualizado com sucesso.');
    }

    public function destroy(MomentoLiturgico $momentoLiturgico): RedirectResponse
    {
        $momentoLiturgico->update(['ativo' => false]);

        return redirect()
            ->route($this->routeName('momentos-liturgicos.index'))
            ->with('success', 'Momento liturgico inativado com sucesso.');
    }

    private function routePrefix(): string
    {
        return request()->routeIs('coordenador.*') ? 'coordenador' : 'admin';
    }

    private function routeName(string $name): string
    {
        return $this->routePrefix() . '.' . $name;
    }
}
