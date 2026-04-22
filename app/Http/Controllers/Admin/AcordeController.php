<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Acorde;
use App\Services\NotificacaoSistemaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AcordeController extends Controller
{
    public function __construct(
        private readonly NotificacaoSistemaService $notificacaoSistemaService
    ) {
    }

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

        $acordes = $consulta
            ->orderBy('nome')
            ->orderBy('descricao')
            ->latest('id')
            ->paginate(12);
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

        $nome = trim((string) ($dados['nome'] ?? $dados['name'] ?? ''));
        $descricao = trim((string) ($dados['descricao'] ?? $dados['description'] ?? ''));

        if (!$nome) {
            return back()->withErrors([
                'nome' => 'O nome do acorde e obrigatorio.',
            ])->withInput();
        }

        if ($descricao === '' && Acorde::where('nome', $nome)->exists()) {
            return back()->withErrors([
                'descricao' => 'Esse acorde ja possui outras variacoes. Preencha a descricao visual para diferenciar este shape.',
            ])->withInput();
        }

        $consultaDuplicada = Acorde::query()->where('nome', $nome);

        if ($descricao === '') {
            $consultaDuplicada->where(function ($query) {
                $query->whereNull('descricao')->orWhere('descricao', '');
            });
        } else {
            $consultaDuplicada->where('descricao', $descricao);
        }

        if ($consultaDuplicada->exists()) {
            return back()->withErrors([
                'descricao' => 'Ja existe uma variacao desse acorde com a mesma descricao visual.',
            ])->withInput();
        }

        /** @var \App\Models\Usuario|null $ator */
        $ator = Auth::user();

        $acorde = Acorde::create([
            'nome' => $nome,
            'descricao' => $descricao !== '' ? $descricao : null,
            'dados_diagrama' => $this->normalizarDadosDiagrama($dados['dados_diagrama'] ?? $dados['shape'] ?? null),
            'ativo' => true,
        ]);

        $this->notificacaoSistemaService->enviarParaTodosUsuariosAtivos(
            evento: 'acorde_cadastrado',
            ator: $ator,
            contexto: [
                'origem' => 'admin_acordes_store',
                'origem_id' => $acorde->id,
                'nome' => $acorde->nome,
            ]
        );

        $totalAcordesAtivos = Acorde::query()->where('ativo', true)->count();
        if ($totalAcordesAtivos > 0 && $totalAcordesAtivos % 100 === 0) {
            $this->notificacaoSistemaService->enviarParaTodosUsuariosAtivos(
                evento: 'acordes_marco_alcancado',
                ator: $ator,
                contexto: [
                    'origem' => 'admin_acordes_store_marco',
                    'origem_id' => $acorde->id,
                    'nome' => $acorde->nome,
                    'quantidade' => $totalAcordesAtivos,
                ]
            );
        }

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

        $nome = trim((string) ($dados['nome'] ?? $dados['name'] ?? ''));
        $descricao = trim((string) ($dados['descricao'] ?? $dados['description'] ?? ''));

        if (!$nome) {
            return back()->withErrors([
                'nome' => 'O nome do acorde e obrigatorio.',
            ])->withInput();
        }

        if ($descricao === '' && Acorde::where('nome', $nome)->whereKeyNot($acorde->id)->exists()) {
            return back()->withErrors([
                'descricao' => 'Esse acorde ja possui outras variacoes. Preencha a descricao visual para diferenciar este shape.',
            ])->withInput();
        }

        $consultaDuplicada = Acorde::query()
            ->where('nome', $nome)
            ->whereKeyNot($acorde->id);

        if ($descricao === '') {
            $consultaDuplicada->where(function ($query) {
                $query->whereNull('descricao')->orWhere('descricao', '');
            });
        } else {
            $consultaDuplicada->where('descricao', $descricao);
        }

        if ($consultaDuplicada->exists()) {
            return back()->withErrors([
                'descricao' => 'Ja existe outra variacao desse acorde com a mesma descricao visual.',
            ])->withInput();
        }

        $acorde->update([
            'nome' => $nome,
            'descricao' => $descricao !== '' ? $descricao : null,
            'dados_diagrama' => $this->normalizarDadosDiagrama($dados['dados_diagrama'] ?? $dados['shape'] ?? null),
            'ativo' => (bool) ($dados['ativo'] ?? $acorde->ativo),
        ]);

        return redirect()
            ->route('admin.acordes.index')
            ->with('success', 'Acorde atualizado com sucesso.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $acorde = Acorde::findOrFail($id);
        $acorde->update([
            'ativo' => false,
        ]);

        /** @var \App\Models\Usuario|null $ator */
        $ator = Auth::user();
        $this->notificacaoSistemaService->enviarParaTodosUsuariosAtivos(
            evento: 'acorde_inativado',
            ator: $ator,
            contexto: [
                'origem' => 'admin_acordes_destroy',
                'origem_id' => $acorde->id,
                'nome' => $acorde->nome,
            ]
        );

        return redirect()
            ->route('admin.acordes.index')
            ->with('success', 'Acorde inativado com sucesso.');
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
