<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TempoLiturgico;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TempoLiturgicoController extends Controller
{
    public function index(): View
    {
        return view('admin.tempos-liturgicos.index', [
            'temposLiturgicos' => TempoLiturgico::orderBy('nome')->get(),
            'routePrefix' => $this->routePrefix(),
        ]);
    }

    public function create(): View
    {
        return view('admin.tempos-liturgicos.create', [
            'routePrefix' => $this->routePrefix(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $dados = $request->validate([
            'nome' => ['required', 'string', 'max:255', Rule::unique('classificacoes_liturgicas', 'nome')->where(fn ($query) => $query->where('tipo', 'tempo'))],
            'descricao' => ['nullable', 'string'],
            'ativo' => ['nullable', 'boolean'],
        ]);

        TempoLiturgico::create([
            'nome' => $dados['nome'],
            'descricao' => $dados['descricao'] ?? null,
            'ativo' => (bool) ($dados['ativo'] ?? true),
        ]);

        return redirect()
            ->route($this->routeName('tempos-liturgicos.index'))
            ->with('success', 'Tempo liturgico cadastrado com sucesso.');
    }

    public function edit(TempoLiturgico $tempoLiturgico): View
    {
        return view('admin.tempos-liturgicos.edit', [
            'tempoLiturgico' => $tempoLiturgico,
            'routePrefix' => $this->routePrefix(),
        ]);
    }

    public function update(Request $request, TempoLiturgico $tempoLiturgico): RedirectResponse
    {
        $dados = $request->validate([
            'nome' => ['required', 'string', 'max:255', Rule::unique('classificacoes_liturgicas', 'nome')->where(fn ($query) => $query->where('tipo', 'tempo'))->ignore($tempoLiturgico->id)],
            'descricao' => ['nullable', 'string'],
            'ativo' => ['nullable', 'boolean'],
        ]);

        $tempoLiturgico->update([
            'nome' => $dados['nome'],
            'descricao' => $dados['descricao'] ?? null,
            'ativo' => (bool) ($dados['ativo'] ?? false),
        ]);

        return redirect()
            ->route($this->routeName('tempos-liturgicos.index'))
            ->with('success', 'Tempo liturgico atualizado com sucesso.');
    }

    public function destroy(TempoLiturgico $tempoLiturgico): RedirectResponse
    {
        $tempoLiturgico->update(['ativo' => false]);

        return redirect()
            ->route($this->routeName('tempos-liturgicos.index'))
            ->with('success', 'Tempo liturgico inativado com sucesso.');
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
