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
        ]);
    }

    public function create(): View
    {
        return view('admin.tempos-liturgicos.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $dados = $request->validate([
            'nome' => ['required', 'string', 'max:255', Rule::unique('tempos_liturgicos', 'nome')],
            'descricao' => ['nullable', 'string'],
            'ativo' => ['nullable', 'boolean'],
        ]);

        TempoLiturgico::create([
            'nome' => $dados['nome'],
            'descricao' => $dados['descricao'] ?? null,
            'ativo' => (bool) ($dados['ativo'] ?? true),
        ]);

        return redirect()
            ->route('admin.tempos-liturgicos.index')
            ->with('success', 'Tempo liturgico cadastrado com sucesso.');
    }

    public function edit(TempoLiturgico $tempoLiturgico): View
    {
        return view('admin.tempos-liturgicos.edit', [
            'tempoLiturgico' => $tempoLiturgico,
        ]);
    }

    public function update(Request $request, TempoLiturgico $tempoLiturgico): RedirectResponse
    {
        $dados = $request->validate([
            'nome' => ['required', 'string', 'max:255', Rule::unique('tempos_liturgicos', 'nome')->ignore($tempoLiturgico->id)],
            'descricao' => ['nullable', 'string'],
            'ativo' => ['nullable', 'boolean'],
        ]);

        $tempoLiturgico->update([
            'nome' => $dados['nome'],
            'descricao' => $dados['descricao'] ?? null,
            'ativo' => (bool) ($dados['ativo'] ?? false),
        ]);

        return redirect()
            ->route('admin.tempos-liturgicos.index')
            ->with('success', 'Tempo liturgico atualizado com sucesso.');
    }

    public function destroy(TempoLiturgico $tempoLiturgico): RedirectResponse
    {
        $tempoLiturgico->delete();

        return redirect()
            ->route('admin.tempos-liturgicos.index')
            ->with('success', 'Tempo liturgico excluido com sucesso.');
    }
}
