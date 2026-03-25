<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MomentoLiturgico;
use App\Models\Musica;
use App\Models\TempoLiturgico;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class MusicaController extends Controller
{
    public function index(Request $request): View
    {
        $consulta = Musica::with(['tempoLiturgico', 'momentoLiturgico', 'criadoPor'])->latest();

        if ($request->filled('search')) {
            $termo = $request->string('search')->toString();

            $consulta->where(function ($query) use ($termo) {
                $query->where('titulo', 'like', "%{$termo}%")
                    ->orWhere('artista', 'like', "%{$termo}%")
                    ->orWhere('letra', 'like', "%{$termo}%");
            });
        }

        $musicas = $consulta->paginate(12)->withQueryString();

        return view('admin.musicas.index', [
            'musicas' => $musicas,
        ]);
    }

    public function create(): View
    {
        return view('admin.musicas.create', [
            'temposLiturgicos' => TempoLiturgico::where('ativo', true)->orderBy('nome')->get(),
            'momentosLiturgicos' => MomentoLiturgico::where('ativo', true)->orderByRaw('ordem_exibicao asc nulls last')->orderBy('nome')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $dados = $this->validarDados($request);

        /** @var \App\Models\Usuario $usuario */
        $usuario = Auth::user();

        $musica = Musica::create([
            'titulo' => $dados['titulo'],
            'artista' => $dados['artista'] ?? null,
            'letra' => $dados['letra'],
            'tempo_liturgico_id' => $dados['tempo_liturgico_id'] ?? null,
            'momento_liturgico_id' => $dados['momento_liturgico_id'] ?? null,
            'criado_por' => $usuario->id,
            'ativo' => (bool) ($dados['ativo'] ?? true),
        ]);

        return redirect()
            ->route('admin.versoes-musicais.create', $musica)
            ->with('success', 'Musica cadastrada com sucesso. Agora cadastre a versao musical com cifras, tom e bpm.');
    }

    public function show(Musica $musica): View
    {
        $musica->load([
            'tempoLiturgico',
            'momentoLiturgico',
            'criadoPor',
            'versoesMusicais' => fn ($query) => $query->with('criadoPor')->latest(),
        ]);

        return view('admin.musicas.show', [
            'musica' => $musica,
        ]);
    }

    public function edit(Musica $musica): View
    {
        return view('admin.musicas.edit', [
            'musica' => $musica,
            'temposLiturgicos' => TempoLiturgico::where('ativo', true)->orderBy('nome')->get(),
            'momentosLiturgicos' => MomentoLiturgico::where('ativo', true)->orderByRaw('ordem_exibicao asc nulls last')->orderBy('nome')->get(),
        ]);
    }

    public function update(Request $request, Musica $musica): RedirectResponse
    {
        $dados = $this->validarDados($request);

        $musica->update([
            'titulo' => $dados['titulo'],
            'artista' => $dados['artista'] ?? null,
            'letra' => $dados['letra'],
            'tempo_liturgico_id' => $dados['tempo_liturgico_id'] ?? null,
            'momento_liturgico_id' => $dados['momento_liturgico_id'] ?? null,
            'ativo' => (bool) ($dados['ativo'] ?? false),
        ]);

        return redirect()
            ->route('admin.musicas.index')
            ->with('success', 'Musica atualizada com sucesso.');
    }

    public function destroy(Musica $musica): RedirectResponse
    {
        $musica->delete();

        return redirect()
            ->route('admin.musicas.index')
            ->with('success', 'Musica excluida com sucesso.');
    }

    private function validarDados(Request $request): array
    {
        $dados = $request->validate(
            [
                'titulo' => ['required', 'string', 'max:255'],
                'artista' => ['nullable', 'string', 'max:255'],
                'letra' => ['required', 'string'],
                'tempo_liturgico_id' => ['nullable', 'exists:tempos_liturgicos,id'],
                'momento_liturgico_id' => ['nullable', 'exists:momentos_liturgicos,id'],
                'ativo' => ['nullable', 'boolean'],
            ],
            [
                'titulo.required' => 'Informe o titulo da musica base.',
                'letra.required' => 'Informe a letra da musica base.',
                'tempo_liturgico_id.exists' => 'O tempo liturgico escolhido nao foi encontrado.',
                'momento_liturgico_id.exists' => 'O momento liturgico escolhido nao foi encontrado.',
            ]
        );

        $dados['titulo'] = trim((string) $dados['titulo']);
        $dados['artista'] = isset($dados['artista']) && $dados['artista'] !== null
            ? trim((string) $dados['artista'])
            : null;
        $dados['letra'] = trim((string) $dados['letra']);

        if ($this->possuiCifrasNaLetraBase($dados['letra'])) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'letra' => 'Voce inseriu cifras na letra. Cadastre apenas a letra aqui. As cifras devem ser adicionadas na versao musical.',
            ]);
        }

        return $dados;
    }

    private function possuiCifrasNaLetraBase(string $letra): bool
    {
        if (preg_match('/\[[^\]]+\]/', $letra) === 1) {
            return true;
        }

        $linhas = preg_split('/\r\n|\r|\n/', $letra) ?: [];

        foreach ($linhas as $linha) {
            if ($this->linhaContemApenasAcordes($linha)) {
                return true;
            }
        }

        return false;
    }

    private function linhaContemApenasAcordes(string $linha): bool
    {
        $linha = trim($linha);

        if ($linha === '') {
            return false;
        }

        $tokens = preg_split('/\s+/', $linha) ?: [];

        if ($tokens === []) {
            return false;
        }

        foreach ($tokens as $token) {
            if (! $this->ehAcorde($token)) {
                return false;
            }
        }

        return true;
    }

    private function ehAcorde(string $token): bool
    {
        $token = trim($token);

        if ($token === '') {
            return false;
        }

        return preg_match('/^[A-G](?:#|b)?(?:m|maj|min|sus|add|dim|aug|º|°)?(?:\d+)?(?:\/[A-G](?:#|b)?)?$/i', $token) === 1;
    }
}
