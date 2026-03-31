<?php

namespace App\Http\Controllers\LocalAdmin;

use App\Http\Controllers\Controller;
use App\Models\Igreja;
use App\Models\Missa;
use App\Models\MissaMusica;
use App\Models\MomentoLiturgico;
use App\Models\Musica;
use App\Models\Padre;
use App\Models\TempoLiturgico;
use App\Models\Usuario;
use App\Models\VersaoMusical;
use App\Services\FolhaVersaoMusicalService;
use App\Rules\ValidChord;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\CarbonImmutable;
use App\Services\RenderizadorCifrasHtmlService;
use App\Services\TranspositorCifrasService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class MissaController extends Controller
{
    public function __construct(
        private readonly TranspositorCifrasService $transpositorCifrasService,
        private readonly RenderizadorCifrasHtmlService $renderizadorCifrasHtmlService,
        private readonly FolhaVersaoMusicalService $folhaVersaoMusicalService
    ) {
    }

    public function index(): View
    {
        $igreja = $this->obterIgreja();
        $this->sincronizarMissasEncerradas($igreja);

        $missas = Missa::with(['tempoLiturgico', 'padre'])
            ->withCount('missaMusicas')
            ->where('igreja_id', $igreja->id)
            ->orderByDesc('data_missa')
            ->orderByDesc('hora_inicio')
            ->get();

        return view('local-admin.missas.index', [
            'igreja' => $this->adicionarDadosPublicos($igreja),
            'missas' => $missas,
        ]);
    }

    public function create(): View
    {
        $igreja = $this->obterIgreja();
        $this->sincronizarMissasEncerradas($igreja);

        return view('local-admin.missas.create', [
            'igreja' => $this->adicionarDadosPublicos($igreja),
            'missa' => new Missa(),
            'temposLiturgicos' => TempoLiturgico::where('ativo', true)->orderBy('nome')->get(),
            'padres' => Padre::query()->orderBy('nome')->get(),
            'missasAnteriores' => Missa::query()
                ->where('igreja_id', $igreja->id)
                ->orderByDesc('data_missa')
                ->orderByDesc('hora_inicio')
                ->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $dados = $this->validarDadosMissa($request);
        $igreja = $this->obterIgreja();

        $missa = DB::transaction(function () use ($dados, $igreja): Missa {
            if (($dados['ativo'] ?? false) === true) {
                Missa::where('igreja_id', $igreja->id)->update(['ativo' => false]);
            }

            $missa = Missa::create([
                'igreja_id' => $igreja->id,
                'padre_id' => $dados['padre_id'] ?? null,
                'tempo_liturgico_id' => $dados['tempo_liturgico_id'] ?? null,
                'titulo' => $dados['titulo'],
                'data_missa' => $dados['data_missa'],
                'hora_inicio' => $dados['hora_inicio'],
                'hora_fim' => $dados['hora_fim'],
                'observacoes' => $dados['observacoes'] ?? null,
                'ativo' => (bool) ($dados['ativo'] ?? true),
            ]);

            if (!empty($dados['reaproveitar_repertorio']) && !empty($dados['missa_origem_id'])) {
                $missaOrigem = Missa::query()
                    ->where('igreja_id', $igreja->id)
                    ->whereKey($dados['missa_origem_id'])
                    ->with(['missaMusicas' => fn ($query) => $query->orderBy('ordem')])
                    ->first();

                if ($missaOrigem) {
                    foreach ($missaOrigem->missaMusicas as $itemOrigem) {
                        MissaMusica::create([
                            'missa_id' => $missa->id,
                            'musica_id' => $itemOrigem->musica_id,
                            'versao_musical_id' => $itemOrigem->versao_musical_id,
                            'tom_usado' => $itemOrigem->tom_usado,
                            'momento_liturgico_id' => $itemOrigem->momento_liturgico_id,
                            'ordem' => $itemOrigem->ordem,
                        ]);
                    }
                }
            }

            return $missa;
        });

        return redirect()
            ->route('local-admin.missas.show', $missa)
            ->with('success', !empty($dados['reaproveitar_repertorio']) && !empty($dados['missa_origem_id'])
                ? 'Missa cadastrada com sucesso. O repertório anterior foi copiado como ponto de partida.'
                : 'Missa cadastrada com sucesso. Agora voce pode montar o repertorio.');
    }

    public function show(Missa $missa): View
    {
        $this->garantirMissaDaIgreja($missa);
        $igreja = $this->obterIgreja();
        $this->sincronizarMissasEncerradas($igreja);
        $missa->refresh();

        $missa->load([
            'tempoLiturgico',
            'padre',
            'missaMusicas' => fn ($query) => $query
                ->with([
                    'musica.tempoLiturgico',
                    'musica.momentoLiturgico',
                    'musica.versoesMusicais' => fn ($subQuery) => $subQuery->where('ativo', true)->orderBy('titulo'),
                    'versaoMusical',
                    'momentoLiturgico',
                ])
                ->orderBy('ordem'),
        ]);

        $musicas = Musica::with(['tempoLiturgico', 'momentoLiturgico', 'versoesMusicais' => fn ($query) => $query->where('ativo', true)->orderBy('titulo')])
            ->where('ativo', true)
            ->orderBy('titulo')
            ->get();

        return view('local-admin.missas.show', [
            'igreja' => $this->adicionarDadosPublicos($igreja),
            'missa' => $missa,
            'musicas' => $musicas,
            'versoesMusicais' => VersaoMusical::with('musica')
                ->where('ativo', true)
                ->orderBy('titulo')
                ->get(),
            'momentosLiturgicos' => MomentoLiturgico::where('ativo', true)->orderByRaw('ordem_exibicao asc nulls last')->orderBy('nome')->get(),
        ]);
    }

    public function edit(Missa $missa): View
    {
        $this->garantirMissaDaIgreja($missa);
        $igreja = $this->obterIgreja();
        $this->sincronizarMissasEncerradas($igreja);
        $missa->refresh();

        return view('local-admin.missas.edit', [
            'igreja' => $this->adicionarDadosPublicos($igreja),
            'missa' => $missa,
            'temposLiturgicos' => TempoLiturgico::where('ativo', true)->orderBy('nome')->get(),
            'padres' => Padre::query()->orderBy('nome')->get(),
        ]);
    }

    public function update(Request $request, Missa $missa): RedirectResponse
    {
        $this->garantirMissaDaIgreja($missa);
        $dados = $this->validarDadosMissa($request);

        DB::transaction(function () use ($dados, $missa): void {
            if (($dados['ativo'] ?? false) === true) {
                Missa::where('igreja_id', $missa->igreja_id)
                    ->where('id', '!=', $missa->id)
                    ->update(['ativo' => false]);
            }

            $missa->update([
                'padre_id' => $dados['padre_id'] ?? null,
                'tempo_liturgico_id' => $dados['tempo_liturgico_id'] ?? null,
                'titulo' => $dados['titulo'],
                'data_missa' => $dados['data_missa'],
                'hora_inicio' => $dados['hora_inicio'],
                'hora_fim' => $dados['hora_fim'],
                'observacoes' => $dados['observacoes'] ?? null,
                'ativo' => (bool) ($dados['ativo'] ?? false),
            ]);
        });

        return redirect()
            ->route('local-admin.missas.show', $missa)
            ->with('success', 'Missa atualizada com sucesso.');
    }

    public function destroy(Missa $missa): RedirectResponse
    {
        $this->garantirMissaDaIgreja($missa);

        $titulo = $missa->titulo;
        $missa->delete();

        return redirect()
            ->route('local-admin.missas.index')
            ->with('success', 'Missa "' . $titulo . '" excluida com sucesso.');
    }

    public function toggle(Missa $missa): RedirectResponse
    {
        $this->garantirMissaDaIgreja($missa);
        $igreja = $this->obterIgreja();
        $this->sincronizarMissasEncerradas($igreja);
        $missa->refresh();

        if ($this->missaJaEncerrada($missa)) {
            return back()->withErrors([
                'missa' => 'Nao e possivel ativar uma missa cujo horario de termino ja passou.',
            ]);
        }

        DB::transaction(function () use ($missa): void {
            $novoStatus = !$missa->ativo;

            if ($novoStatus) {
                Missa::where('igreja_id', $missa->igreja_id)->update(['ativo' => false]);
            }

            $missa->update(['ativo' => $novoStatus]);
        });

        return back()->with('success', $missa->ativo ? 'Missa ativada com sucesso.' : 'Missa desativada com sucesso.');
    }

    public function storeRepertorio(Request $request, Missa $missa): RedirectResponse
    {
        $this->garantirMissaDaIgreja($missa);

        $dados = $request->validate([
            'musica_id' => ['required', 'exists:musicas,id'],
            'versao_musical_id' => ['nullable', 'exists:versoes_musicais,id'],
            'tom_usado' => ['nullable', 'string', 'max:20', new ValidChord()],
            'momento_liturgico_id' => ['nullable', 'exists:momentos_liturgicos,id'],
        ], [
            'musica_id.required' => 'Selecione uma musica para adicionar ao repertorio.',
        ]);

        if (!empty($dados['versao_musical_id'])) {
            $versao = VersaoMusical::findOrFail($dados['versao_musical_id']);
            if ((int) $versao->musica_id !== (int) $dados['musica_id']) {
                return back()->withErrors([
                    'versao_musical_id' => 'A versao musical selecionada nao pertence a musica escolhida.',
                ])->withInput();
            }
        }

        $proximaOrdem = (int) ($missa->missaMusicas()->max('ordem') ?? 0) + 1;

        MissaMusica::create([
            'missa_id' => $missa->id,
            'musica_id' => $dados['musica_id'],
            'versao_musical_id' => $dados['versao_musical_id'] ?? null,
            'tom_usado' => $this->normalizarTomInformado($dados['tom_usado'] ?? null),
            'momento_liturgico_id' => $dados['momento_liturgico_id'] ?? null,
            'ordem' => $proximaOrdem,
        ]);

        return redirect()
            ->route('local-admin.missas.show', $missa)
            ->with('success', 'Musica adicionada ao repertorio da missa.');
    }

    public function updateRepertorio(Request $request, Missa $missa, MissaMusica $missaMusica): RedirectResponse
    {
        $this->garantirMissaDaIgreja($missa);
        $this->garantirItemDaMissa($missa, $missaMusica);

        $dados = $request->validate([
            'versao_musical_id' => ['nullable', 'exists:versoes_musicais,id'],
            'tom_usado' => ['nullable', 'string', 'max:20', new ValidChord()],
            'momento_liturgico_id' => ['nullable', 'exists:momentos_liturgicos,id'],
        ]);

        if (!empty($dados['versao_musical_id'])) {
            $versao = VersaoMusical::findOrFail($dados['versao_musical_id']);
            if ((int) $versao->musica_id !== (int) $missaMusica->musica_id) {
                return back()->withErrors([
                    'versao_musical_id' => 'A versao musical selecionada nao pertence a musica deste item.',
                ]);
            }
        }

        $missaMusica->update([
            'versao_musical_id' => $dados['versao_musical_id'] ?? null,
            'tom_usado' => $this->normalizarTomInformado($dados['tom_usado'] ?? null),
            'momento_liturgico_id' => $dados['momento_liturgico_id'] ?? null,
        ]);

        return back()->with('success', 'Item do repertorio atualizado com sucesso.');
    }

    public function subirRepertorio(Missa $missa, MissaMusica $missaMusica): RedirectResponse
    {
        $this->garantirMissaDaIgreja($missa);
        $this->garantirItemDaMissa($missa, $missaMusica);

        $itemAnterior = MissaMusica::where('missa_id', $missa->id)
            ->where('ordem', '<', $missaMusica->ordem)
            ->orderByDesc('ordem')
            ->first();

        if (!$itemAnterior) {
            return back();
        }

        DB::transaction(function () use ($missaMusica, $itemAnterior): void {
            $ordemAtual = $missaMusica->ordem;
            $ordemDestino = $itemAnterior->ordem;

            // Usa uma ordem temporaria fora da faixa normal para evitar colisao
            // com a unique constraint (missa_id, ordem) durante o swap.
            $missaMusica->update(['ordem' => -$ordemAtual]);
            $itemAnterior->update(['ordem' => $ordemAtual]);
            $missaMusica->update(['ordem' => $ordemDestino]);
        });

        return back()->with('success', 'Item movido para cima.');
    }

    public function descerRepertorio(Missa $missa, MissaMusica $missaMusica): RedirectResponse
    {
        $this->garantirMissaDaIgreja($missa);
        $this->garantirItemDaMissa($missa, $missaMusica);

        $itemSeguinte = MissaMusica::where('missa_id', $missa->id)
            ->where('ordem', '>', $missaMusica->ordem)
            ->orderBy('ordem')
            ->first();

        if (!$itemSeguinte) {
            return back();
        }

        DB::transaction(function () use ($missaMusica, $itemSeguinte): void {
            $ordemAtual = $missaMusica->ordem;
            $ordemDestino = $itemSeguinte->ordem;

            // Usa uma ordem temporaria fora da faixa normal para evitar colisao
            // com a unique constraint (missa_id, ordem) durante o swap.
            $missaMusica->update(['ordem' => -$ordemAtual]);
            $itemSeguinte->update(['ordem' => $ordemAtual]);
            $missaMusica->update(['ordem' => $ordemDestino]);
        });

        return back()->with('success', 'Item movido para baixo.');
    }

    public function destroyRepertorio(Missa $missa, MissaMusica $missaMusica): RedirectResponse
    {
        $this->garantirMissaDaIgreja($missa);
        $this->garantirItemDaMissa($missa, $missaMusica);

        $missaMusica->delete();

        $missa->missaMusicas()
            ->orderBy('ordem')
            ->get()
            ->values()
            ->each(function (MissaMusica $item, int $indice): void {
                $item->update(['ordem' => $indice + 1]);
            });

        return back()->with('success', 'Item removido do repertorio.');
    }

    public function showCifra(Missa $missa, MissaMusica $missaMusica): View
    {
        $this->garantirMissaDaIgreja($missa);
        $this->garantirItemDaMissa($missa, $missaMusica);
        $igreja = $this->obterIgreja();
        $this->sincronizarMissasEncerradas($igreja);
        $missa->refresh();

        $missaMusica->load(['musica', 'versaoMusical', 'momentoLiturgico']);

        return view('local-admin.missas.cifra', [
            'igreja' => $this->adicionarDadosPublicos($igreja),
            'missa' => $missa,
            'itemRepertorio' => $missaMusica,
            'textoCifraExibicao' => $this->obterTextoCifraExibicao($missaMusica),
            'tomOriginal' => $missaMusica->versaoMusical?->tom_musical,
            'tomExibicao' => $missaMusica->tom_exibicao,
        ]);
    }

    public function imprimirCifra(Missa $missa, MissaMusica $missaMusica): View
    {
        $this->garantirMissaDaIgreja($missa);
        $this->garantirItemDaMissa($missa, $missaMusica);
        abort_unless($missaMusica->versaoMusical !== null, 404);

        $folha = $this->montarFolhaItemRepertorio($missa, $missaMusica);

        return view('shared.versao-print', [
            'folha' => $folha,
            'etiquetaFolha' => 'Folha da igreja',
            'pdfUrl' => route('local-admin.repertorio.pdf', [$missa, $missaMusica]),
            'backUrl' => route('local-admin.repertorio.cifra', [$missa, $missaMusica]),
            'pageTitle' => ($missaMusica->musica?->titulo ?: 'Versao') . ' | Impressao',
        ]);
    }

    public function pdfCifra(Missa $missa, MissaMusica $missaMusica)
    {
        $this->garantirMissaDaIgreja($missa);
        $this->garantirItemDaMissa($missa, $missaMusica);
        abort_unless($missaMusica->versaoMusical !== null, 404);

        $folha = $this->montarFolhaItemRepertorio($missa, $missaMusica);

        return Pdf::loadView('shared.versao-pdf', [
            'folha' => $folha,
            'etiquetaFolha' => 'Folha da igreja',
            'pageTitle' => ($missaMusica->musica?->titulo ?: 'Versao') . ' | PDF',
        ])
            ->setPaper('a4', 'portrait')
            ->download('missa-' . $missa->id . '-musica-' . $missaMusica->id . '.pdf');
    }

    public function apresentacao(Missa $missa): View
    {
        $this->garantirMissaDaIgreja($missa);
        $igreja = $this->obterIgreja();
        $this->sincronizarMissasEncerradas($igreja);
        $missa->refresh();

        $missa->load([
            'tempoLiturgico',
            'padre',
            'missaMusicas' => fn ($query) => $query
                ->with(['musica', 'versaoMusical', 'momentoLiturgico'])
                ->orderBy('ordem'),
        ]);

        $itensApresentacao = $missa->missaMusicas
            ->filter(fn (MissaMusica $item) => $item->versaoMusical !== null)
            ->values()
            ->map(function (MissaMusica $item): array {
                return [
                    'id' => $item->id,
                    'ordem' => $item->ordem,
                    'titulo' => $item->musica->titulo,
                    'artista' => $item->musica->artista,
                    'momento' => $item->momentoLiturgico?->nome,
                    'versao' => $item->versaoMusical->titulo ?: 'Versao principal',
                    'tom_original' => $item->versaoMusical->tom_musical,
                    'tom_exibicao' => $item->tom_exibicao,
                    'bpm' => $item->versaoMusical->bpm,
                    'letra' => $this->obterTextoCifraExibicao($item),
                ];
            });

        return view('local-admin.missas.apresentacao', [
            'igreja' => $this->adicionarDadosPublicos($igreja),
            'missa' => $missa,
            'itensApresentacao' => $itensApresentacao,
        ]);
    }

    public function pdf(Missa $missa)
    {
        $this->garantirMissaDaIgreja($missa);
        $igreja = $this->obterIgreja();
        $this->sincronizarMissasEncerradas($igreja);
        $missa->refresh();

        $missa->load([
            'igreja',
            'tempoLiturgico',
            'padre',
            'missaMusicas' => fn ($query) => $query
                ->with(['musica', 'versaoMusical', 'momentoLiturgico'])
                ->orderBy('ordem'),
        ]);

        $itensPdf = $missa->missaMusicas->map(function (MissaMusica $item): array {
            $texto = $item->versaoMusical ? $this->obterTextoCifraExibicao($item) : '';

            return [
                'ordem' => $item->ordem,
                'momento' => $item->momentoLiturgico?->nome,
                'musica' => $item->musica?->titulo,
                'versao' => $item->versaoMusical?->titulo ?: 'Nao vinculada',
                'tom_original' => $item->versaoMusical?->tom_musical,
                'tom_exibicao' => $item->tom_exibicao,
                'bpm' => $item->versaoMusical?->bpm,
                'html_cifra' => $texto !== '' ? $this->renderizadorCifrasHtmlService->renderizar($texto) : null,
            ];
        });

        $pdf = Pdf::loadView('local-admin.missas.pdf', [
            'missa' => $missa,
            'itensPdf' => $itensPdf,
        ])->setPaper('a4', 'portrait');

        return $pdf->download('missa-' . $missa->id . '.pdf');
    }

    private function validarDadosMissa(Request $request): array
    {
        $hoje = CarbonImmutable::now('America/Cuiaba')->startOfDay();
        $dataMinima = $hoje->subMonth()->toDateString();
        $dataMaxima = $hoje->addMonth()->toDateString();

        $validator = Validator::make($request->all(), [
            'titulo' => ['required', 'string', 'max:255'],
            'tempo_liturgico_id' => ['nullable', 'exists:tempos_liturgicos,id'],
            'padre_id' => ['nullable', 'exists:padres,id'],
            'data_missa' => ['required', 'date', 'after_or_equal:' . $dataMinima, 'before_or_equal:' . $dataMaxima],
            'hora_inicio' => ['required', 'date_format:H:i'],
            'hora_fim' => ['required', 'date_format:H:i'],
            'observacoes' => ['nullable', 'string'],
            'ativo' => ['nullable', 'boolean'],
            'reaproveitar_repertorio' => ['nullable', 'boolean'],
            'missa_origem_id' => ['nullable', 'exists:missas,id'],
        ], [
            'titulo.required' => 'Informe o titulo da missa.',
            'data_missa.required' => 'Informe a data da missa.',
            'data_missa.after_or_equal' => 'A data da missa nao pode ser anterior a 1 mes atras.',
            'data_missa.before_or_equal' => 'A data da missa nao pode ser posterior a 1 mes a frente.',
            'hora_inicio.required' => 'Informe o horario de inicio.',
            'hora_fim.required' => 'Informe o horario de termino.',
            'hora_inicio.date_format' => 'Informe o horario de inicio no formato HH:MM.',
            'hora_fim.date_format' => 'Informe o horario de termino no formato HH:MM.',
        ]);

        $validator->after(function ($validator) use ($request): void {
            $horaInicio = (string) $request->input('hora_inicio', '');
            $horaFim = (string) $request->input('hora_fim', '');

            if ($horaInicio !== '' && $horaFim !== '' && $horaInicio === $horaFim) {
                $validator->errors()->add(
                    'hora_fim',
                    'O horario de termino deve ser diferente do horario de inicio.'
                );
            }
        });

        return $validator->validate();
    }

    private function obterUsuario(): Usuario
    {
        /** @var \App\Models\Usuario $usuario */
        $usuario = Auth::user();

        abort_unless($usuario && $usuario->ehAdminLocal(), 403);

        return $usuario;
    }

    private function obterIgreja(): Igreja
    {
        $igreja = $this->obterUsuario()->igreja;

        abort_unless($igreja !== null, 404, 'Igreja nao encontrada para este administrador local.');

        return $igreja;
    }

    private function garantirMissaDaIgreja(Missa $missa): void
    {
        abort_unless((int) $missa->igreja_id === (int) $this->obterIgreja()->id, 404);
    }

    private function garantirItemDaMissa(Missa $missa, MissaMusica $missaMusica): void
    {
        abort_unless((int) $missaMusica->missa_id === (int) $missa->id, 404);
    }

    private function adicionarDadosPublicos(Igreja $igreja): Igreja
    {
        $linkPublico = route('igrejas.public.show', ['slug' => $igreja->slug]);

        $igreja->setAttribute('link_publico', $linkPublico);
        $igreja->setAttribute(
            'qr_code_url',
            'https://api.qrserver.com/v1/create-qr-code/?size=260x260&data=' . urlencode($linkPublico)
        );

        return $igreja;
    }

    private function sincronizarMissasEncerradas(Igreja $igreja): void
    {
        $agora = CarbonImmutable::now('America/Cuiaba');
        $idsEncerrados = Missa::query()
            ->where('igreja_id', $igreja->id)
            ->where('ativo', true)
            ->get()
            ->filter(fn (Missa $missa): bool => $missa->dataHoraFim('America/Cuiaba')->lessThan($agora))
            ->pluck('id');

        if ($idsEncerrados->isEmpty()) {
            return;
        }

        Missa::query()
            ->whereKey($idsEncerrados)
            ->update(['ativo' => false]);
    }

    private function missaJaEncerrada(Missa $missa): bool
    {
        $agora = CarbonImmutable::now('America/Cuiaba');

        return $missa->dataHoraFim('America/Cuiaba')->lessThan($agora);
    }

    private function obterTextoCifraExibicao(MissaMusica $item): string
    {
        $textoOriginal = $item->versaoMusical?->letra_com_cifras ?? '';
        $passos = $this->transpositorCifrasService->calcularPassos(
            $item->versaoMusical?->tom_musical,
            $item->tom_exibicao
        );

        return $this->transpositorCifrasService->transporTextoCifrado($textoOriginal, $passos);
    }

    private function normalizarTomInformado(?string $tom): ?string
    {
        $tom = trim((string) $tom);

        return $tom !== '' ? $tom : null;
    }

    private function montarFolhaItemRepertorio(Missa $missa, MissaMusica $missaMusica): array
    {
        $missaMusica->loadMissing(['musica', 'versaoMusical', 'momentoLiturgico']);

        return $this->folhaVersaoMusicalService->montar(
            $missaMusica->versaoMusical,
            $this->obterTextoCifraExibicao($missaMusica),
            $missaMusica->tom_exibicao,
            [
                'Missa' => $missa->titulo,
                'Momento' => $missaMusica->momentoLiturgico?->nome,
                'Igreja' => $missa->igreja?->nome ?? $this->obterIgreja()->nome,
            ]
        );
    }
}
