<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MomentoLiturgico;
use App\Models\Musica;
use App\Models\TempoLiturgico;
use App\Services\AuditoriaOperacionalService;
use App\Services\NotificacaoSistemaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MusicaController extends Controller
{
    public function __construct(
        private readonly AuditoriaOperacionalService $auditoriaOperacionalService,
        private readonly NotificacaoSistemaService $notificacaoSistemaService
    ) {
    }

    public function index(Request $request): View
    {
        $momentoFiltro = $request->integer('momento_liturgico_id') ?: null;
        $consulta = Musica::with(['tempoLiturgico', 'momentoLiturgico', 'criadoPor'])
            ->withCount([
                'versoesMusicais as versoes_ativas_count' => fn ($query) => $query->where('ativo', true),
            ])
            ->when($momentoFiltro, fn ($query) => $query->where('momento_liturgico_id', $momentoFiltro))
            ->latest();

        if ($request->filled('search')) {
            $termo = trim($request->string('search')->toString());
            $operadorBusca = DB::getDriverName() === 'pgsql' ? 'ilike' : 'like';

            $consulta->where(function ($query) use ($termo, $operadorBusca) {
                $query->where('titulo', $operadorBusca, "%{$termo}%")
                    ->orWhere('artista', $operadorBusca, "%{$termo}%")
                    ->orWhere('letra', $operadorBusca, "%{$termo}%");
            });
        }

        $sugestoesMusicas = Musica::query()
            ->select('titulo', 'artista')
            ->orderBy('titulo')
            ->limit(200)
            ->get()
            ->map(fn (Musica $musica) => [
                'titulo' => $musica->titulo,
                'artista' => $musica->artista,
            ])
            ->values();

        $musicas = $consulta->paginate(12)->withQueryString();

        return view('admin.musicas.index', [
            'musicas' => $musicas,
            'sugestoesMusicas' => $sugestoesMusicas,
            'momentosLiturgicos' => MomentoLiturgico::where('ativo', true)->orderByRaw('ordem_exibicao asc nulls last')->orderBy('nome')->get(),
            'momentoFiltro' => $momentoFiltro,
            'routePrefix' => request()->routeIs('coordenador.*') ? 'coordenador' : 'admin',
            'podeInativar' => $this->podeInativarRegistros(),
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
        $duplicidades = $this->detectarMusicasDuplicadas($dados);

        if ($duplicidades->isNotEmpty() && !((bool) ($dados['confirmar_duplicidade'] ?? false))) {
            return back()
                ->withInput()
                ->with('duplicidade_musica', [
                    'mensagem' => 'Ja existe uma musica com titulo e artista parecidos. Deseja continuar mesmo assim?',
                    'musicas' => $duplicidades->all(),
                ]);
        }

        /** @var \App\Models\Usuario $usuario */
        $usuario = Auth::user();

        $musica = Musica::create([
            'titulo' => $dados['titulo'],
            'artista' => $dados['artista'] ?? null,
            'letra' => $dados['letra'],
            'tempo_liturgico_id' => $dados['tempo_liturgico_id'] ?? null,
            'momento_liturgico_id' => $dados['momento_liturgico_id'] ?? null,
            'criado_por' => $usuario->id,
            'ativo' => $this->podeInativarRegistros() ? (bool) ($dados['ativo'] ?? true) : true,
        ]);

        $this->auditoriaOperacionalService->registrar(
            evento: 'musica_criada',
            ator: $usuario,
            igreja: null,
            contexto: [
                'origem' => 'admin_musicas_store',
                'origem_id' => $musica->id,
                'titulo' => $musica->titulo,
                'artista' => $musica->artista,
                'resumo' => 'Musica base criada na biblioteca central.',
            ]
        );

        $this->notificacaoSistemaService->enviarParaUsuariosOperacionaisAtivos(
            evento: 'musica_cadastrada',
            ator: $usuario,
            contexto: [
                'origem' => 'admin_musicas_store',
                'origem_id' => $musica->id,
                'titulo' => $musica->titulo,
            ]
        );

        return redirect()
            ->route($this->routeName('musicas.show'), $musica)
            ->with('success', 'Musica cadastrada com sucesso. Quando quiser, use o botao Cadastrar cifra para incluir tom, bpm e letra com cifras.');
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
        $duplicidades = $this->detectarMusicasDuplicadas($dados, $musica->id);

        if ($duplicidades->isNotEmpty() && !((bool) ($dados['confirmar_duplicidade'] ?? false))) {
            return back()
                ->withInput()
                ->with('duplicidade_musica', [
                    'mensagem' => 'Ja existe uma musica com titulo e artista parecidos. Deseja continuar mesmo assim?',
                    'musicas' => $duplicidades->all(),
                ]);
        }

        /** @var \App\Models\Usuario|null $usuario */
        $usuario = Auth::user();

        $musica->update([
            'titulo' => $dados['titulo'],
            'artista' => $dados['artista'] ?? null,
            'letra' => $dados['letra'],
            'tempo_liturgico_id' => $dados['tempo_liturgico_id'] ?? null,
            'momento_liturgico_id' => $dados['momento_liturgico_id'] ?? null,
            'ativo' => $this->podeInativarRegistros() ? (bool) ($dados['ativo'] ?? false) : (bool) $musica->ativo,
        ]);

        $this->auditoriaOperacionalService->registrar(
            evento: 'musica_editada',
            ator: $usuario,
            igreja: null,
            contexto: [
                'origem' => 'admin_musicas_update',
                'origem_id' => $musica->id,
                'titulo' => $musica->titulo,
                'artista' => $musica->artista,
                'resumo' => 'Musica base atualizada na biblioteca central.',
            ]
        );

        return redirect()
            ->route($this->routeName('musicas.index'))
            ->with('success', 'Musica atualizada com sucesso.');
    }

    public function destroy(Musica $musica): RedirectResponse
    {
        $musica->update([
            'ativo' => false,
        ]);

        /** @var \App\Models\Usuario|null $usuario */
        $usuario = Auth::user();
        $this->auditoriaOperacionalService->registrar(
            evento: 'musica_inativada',
            ator: $usuario,
            igreja: null,
            contexto: [
                'origem' => 'admin_musicas_destroy',
                'origem_id' => $musica->id,
                'titulo' => $musica->titulo,
                'resumo' => 'Musica marcada como inativa na biblioteca central.',
            ]
        );

        $this->notificacaoSistemaService->enviarParaUsuariosOperacionaisAtivos(
            evento: 'musica_inativada',
            ator: $usuario,
            contexto: [
                'origem' => 'admin_musicas_destroy',
                'origem_id' => $musica->id,
                'titulo' => $musica->titulo,
            ]
        );

        return redirect()
            ->route($this->routeName('musicas.index'))
            ->with('success', 'Musica inativada com sucesso.');
    }

    private function routeName(string $sufixo): string
    {
        $nomeAtual = request()->route()?->getName() ?? '';

        if (str_starts_with($nomeAtual, 'coordenador.')) {
            return 'coordenador.' . $sufixo;
        }

        return 'admin.' . $sufixo;
    }

    private function podeInativarRegistros(): bool
    {
        $nomeAtual = request()->route()?->getName() ?? '';

        return !str_starts_with($nomeAtual, 'coordenador.');
    }

    private function validarDados(Request $request): array
    {
        $dados = $request->validate(
            [
                'titulo' => ['required', 'string', 'max:255'],
                'artista' => ['nullable', 'string', 'max:255'],
                'letra' => ['required', 'string'],
                'tempo_liturgico_id' => ['nullable', Rule::exists('classificacoes_liturgicas', 'id')->where(fn ($query) => $query->where('tipo', 'tempo'))],
                'momento_liturgico_id' => ['nullable', Rule::exists('classificacoes_liturgicas', 'id')->where(fn ($query) => $query->where('tipo', 'momento'))],
                'ativo' => ['nullable', 'boolean'],
                'confirmar_duplicidade' => ['nullable', 'boolean'],
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

    private function detectarMusicasDuplicadas(array $dados, ?int $ignorarMusicaId = null)
    {
        $titulo = $this->normalizarTexto((string) ($dados['titulo'] ?? ''));
        $artista = $this->normalizarTexto((string) ($dados['artista'] ?? ''));

        if ($titulo === '') {
            return collect();
        }

        return Musica::query()
            ->when($ignorarMusicaId, fn ($query) => $query->whereKeyNot($ignorarMusicaId))
            ->get()
            ->filter(function (Musica $musica) use ($titulo, $artista): bool {
                $tituloExistente = $this->normalizarTexto((string) $musica->titulo);
                $artistaExistente = $this->normalizarTexto((string) $musica->artista);

                similar_text($titulo, $tituloExistente, $similaridadeTitulo);
                similar_text($artista, $artistaExistente, $similaridadeArtista);

                $mesmoTitulo = $titulo === $tituloExistente || $similaridadeTitulo >= 88;
                $mesmoArtista = $artista === $artistaExistente
                    || ($artista !== '' && $artistaExistente !== '' && $similaridadeArtista >= 88);

                return $mesmoTitulo && $mesmoArtista;
            })
            ->take(5)
            ->map(fn (Musica $musica) => [
                'titulo' => $musica->titulo,
                'artista' => $musica->artista ?: 'Artista nao informado',
                'ativo' => (bool) $musica->ativo,
            ])
            ->values();
    }

    private function normalizarTexto(string $texto): string
    {
        return mb_strtolower(Str::ascii(trim(preg_replace('/\s+/', ' ', $texto) ?? '')));
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

        return preg_match('/^[A-G](?:#|b)?(?:(?:maj|min|dim|aug|sus|add|omit|no|m|M|º|°|\\+|-|[0-9#b])|\\([^\\)\\]]+\\))*(?:\\/[A-G](?:#|b)?)?$/', $token) === 1;
    }
}
