<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Igreja;
use App\Models\Padre;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PadreController extends Controller
{
    public function index(): View
    {
        return view('admin.padres.index', [
            'padres' => Padre::query()
                ->with('igreja')
                ->orderBy('nome')
                ->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.padres.create', [
            'padre' => new Padre(),
            'igrejas' => Igreja::query()->orderBy('nome')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $dados = $this->validarPadre($request);

        Padre::create([
            'nome' => $dados['nome'],
            'cpf' => $dados['cpf'],
            'igreja_id' => $dados['igreja_id'] ?? null,
            'ativo' => (bool) ($dados['ativo'] ?? true),
        ]);

        return redirect()
            ->route('admin.padres.index')
            ->with('success', 'Padre cadastrado com sucesso.');
    }

    public function edit(Padre $padre): View
    {
        return view('admin.padres.edit', [
            'padre' => $padre,
            'igrejas' => Igreja::query()->orderBy('nome')->get(),
        ]);
    }

    public function update(Request $request, Padre $padre): RedirectResponse
    {
        $dados = $this->validarPadre($request, $padre);

        $padre->update([
            'nome' => $dados['nome'],
            'cpf' => $dados['cpf'],
            'igreja_id' => $dados['igreja_id'] ?? null,
            'ativo' => (bool) ($dados['ativo'] ?? false),
        ]);

        return redirect()
            ->route('admin.padres.index')
            ->with('success', 'Padre atualizado com sucesso.');
    }

    public function toggle(Padre $padre): RedirectResponse
    {
        $padre->update([
            'ativo' => !$padre->ativo,
        ]);

        return redirect()
            ->route('admin.padres.index')
            ->with('success', $padre->ativo ? 'Padre ativado com sucesso.' : 'Padre inativado com sucesso.');
    }

    protected function validarPadre(Request $request, ?Padre $padre = null): array
    {
        return $request->validate([
            'nome' => ['required', 'string', 'max:255'],
            'cpf' => ['required', 'string', 'max:14', Rule::unique('padres', 'cpf')->ignore($padre?->id)],
            'igreja_id' => ['nullable', 'exists:igrejas,id'],
            'ativo' => ['nullable', 'boolean'],
        ]);
    }
}
