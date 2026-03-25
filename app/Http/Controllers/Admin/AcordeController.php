<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Acorde;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AcordeController extends Controller
{
    public function index(Request $request): View
    {
        $consulta = Acorde::query();

        if ($request->filled('search')) {
            $termo = $request->string('search')->toString();
            $consulta->where(function ($query) use ($termo) {
                $query->where('nome', 'like', "%{$termo}%")
                    ->orWhere('descricao', 'like', "%{$termo}%");
            });
        }

        $acordes = $consulta->latest()->paginate(12);
        $acordes->getCollection()->transform(fn (Acorde $acorde) => $this->adaptarAcordeParaView($acorde));

        return view('admin.acordes.index', [
            'acordes' => $acordes,
        ]);
    }

    public function create(): View
    {
        return view('admin.acordes.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $dados = $request->validate([
            'nome' => ['nullable', 'string', 'max:255'],
            'name' => ['nullable', 'string', 'max:255'],
            'descricao' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'dados_diagrama' => ['nullable'],
            'shape' => ['nullable'],
        ]);

        $nome = $dados['nome'] ?? $dados['name'] ?? null;

        if (!$nome) {
            return back()->withErrors([
                'nome' => 'O nome do acorde e obrigatorio.',
            ])->withInput();
        }

        Acorde::create([
            'nome' => $nome,
            'descricao' => $dados['descricao'] ?? $dados['description'] ?? null,
            'dados_diagrama' => $this->normalizarDadosDiagrama($dados['dados_diagrama'] ?? $dados['shape'] ?? null),
            'ativo' => true,
        ]);

        return redirect()
            ->route('admin.acordes.index')
            ->with('success', 'Acorde criado com sucesso.');
    }

    public function show(int $id): View
    {
        $acorde = $this->adaptarAcordeParaView(Acorde::findOrFail($id));

        return view('admin.acordes.show', [
            'acorde' => $acorde,
        ]);
    }

    public function edit(int $id): View
    {
        $acorde = $this->adaptarAcordeParaView(Acorde::findOrFail($id));

        return view('admin.acordes.edit', [
            'acorde' => $acorde,
        ]);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $acorde = Acorde::findOrFail($id);

        $dados = $request->validate([
            'nome' => ['nullable', 'string', 'max:255'],
            'name' => ['nullable', 'string', 'max:255'],
            'descricao' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'dados_diagrama' => ['nullable'],
            'shape' => ['nullable'],
            'ativo' => ['nullable', 'boolean'],
        ]);

        $nome = $dados['nome'] ?? $dados['name'] ?? null;

        if (!$nome) {
            return back()->withErrors([
                'nome' => 'O nome do acorde e obrigatorio.',
            ])->withInput();
        }

        $acorde->update([
            'nome' => $nome,
            'descricao' => $dados['descricao'] ?? $dados['description'] ?? null,
            'dados_diagrama' => $this->normalizarDadosDiagrama($dados['dados_diagrama'] ?? $dados['shape'] ?? null),
            'ativo' => (bool) ($dados['ativo'] ?? $acorde->ativo),
        ]);

        return redirect()
            ->route('admin.acordes.index')
            ->with('success', 'Acorde atualizado com sucesso.');
    }

    public function destroy(int $id): RedirectResponse
    {
        Acorde::findOrFail($id)->delete();

        return redirect()
            ->route('admin.acordes.index')
            ->with('success', 'Acorde excluido com sucesso.');
    }

    protected function adaptarAcordeParaView(Acorde $acorde): Acorde
    {
        $shape = $this->normalizarDadosDiagrama($acorde->dados_diagrama);

        $acorde->setAttribute('shape', $shape);
        $acorde->setAttribute('variation_name', data_get($shape, 'variation_name'));
        $acorde->setAttribute('base_fret', data_get($shape, 'baseFret', 1));
        $acorde->setAttribute('root_note', data_get($shape, 'root_note'));

        return $acorde;
    }

    protected function normalizarDadosDiagrama(mixed $dados): mixed
    {
        if (is_string($dados) && $dados !== '') {
            $decodificado = json_decode($dados, true);

            return json_last_error() === JSON_ERROR_NONE ? $decodificado : $dados;
        }

        return $dados;
    }
}
