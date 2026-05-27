<?php

namespace App\Http\Controllers\LocalAdmin;

use App\Http\Controllers\Controller;
use App\Enums\PapelIgreja;
use App\Models\Igreja;
use App\Models\Missa;
use App\Models\MissaMusica;
use App\Models\MomentoLiturgico;
use App\Models\Musica;
use App\Models\TempoLiturgico;
use App\Models\Usuario;
use App\Models\VersaoMusical;
use App\Services\AuditoriaOperacionalService;
use App\Services\FolhaVersaoMusicalService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\CarbonImmutable;
use App\Services\RenderizadorCifrasHtmlService;
use App\Services\TranspositorCifrasService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MissaController extends Controller
{
    public function __construct(
        private readonly AuditoriaOperacionalService $auditoriaOperacionalService,
        private readonly TranspositorCifrasService $transpositorCifrasService,
        private readonly RenderizadorCifrasHtmlService $renderizadorCifrasHtmlService,
        private readonly FolhaVersaoMusicalService $folhaVersaoMusicalService
    ) {
    }

    public function index(): View
    {
        $igreja = $this->obterIgreja();
        $this->sincronizarMissasEncerradas($igreja);

        $missas = Missa::with(['tempoLiturgico', 'celebrante'])
            ->withCount('missaMusicas')
            ->where('igreja_id', $igreja->id)
            ->orderByDesc('data_missa')
            ->orderByDesc('hora_inicio')
            ->get();

        return view('local-admin.missas.index', [
            'igreja' => $this->adicionarDadosPublicos($igreja),
            'missas' => $missas,
            'igrejasAdministradas' => $this->obterIgrejasAdministradas($this->obterUsuario(), $igreja),
        ]);
    }

    public function create(): View
    {
        $igreja = $this->obterIgreja();
        $this->sincronizarMissasEncerradas($igreja);

        return view('local-admin.missas.create', [
            'igreja' => $this->adicionarDadosPublicos($igreja),
            'missa' => new Missa(),
            'igrejasAdministradas' => $this->obterIgrejasAdministradas($this->obterUsuario(), $igreja),
            'temposLiturgicos' => TempoLiturgico::where('ativo', true)->orderBy('nome')->get(),
            'padres' => Usuario::query()
                ->where('eh_padre', true)
                ->where('ativo', true)
                ->orderBy('nome')
                ->get(),
            'missasAnteriores' => Missa::query()
                ->with(['tempoLiturgico', 'celebrante', 'missaMusicas.musica'])
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
        $usuario = $this->obterUsuario();

        $missa = DB::transaction(function () use ($dados, $igreja): Missa {
            if (($dados['ativo'] ?? false) === true) {
                Missa::where('igreja_id', $igreja->id)->update(['ativo' => false]);
            }

            $missa = Missa::create([
                'igreja_id' => $igreja->id,
                'celebrante_usuario_id' => $dados['padre_id'] ?? null,
                'tempo_liturgico_id' => $dados['tempo_liturgico_id'] ?? null,
                'titulo' => $dados['titulo'],
                'data_missa' => $dados['data_missa'],
                'hora_inicio' => $dados['hora_inicio'],
                'hora_fim' => $dados['hora_fim'],
                'observacoes' => $dados['observacoes'] ?? null,
                'publica_para_fieis' => (bool) ($dados['publica_para_fieis'] ?? false),
                'publica_para_musicos' => (bool) ($dados['publica_para_musicos'] ?? false),
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

        $this->auditoriaOperacionalService->registrar(
            evento: 'missa_criada',
            ator: $usuario,
            igreja: $igreja,
            contexto: [
                'origem' => 'local_admin_missas_store',
                'origem_id' => $missa->id,
                'titulo' => $missa->titulo,
                'publica_para_fieis' => $missa->publica_para_fieis,
                'publica_para_musicos' => $missa->publica_para_musicos,
                'resumo' => !empty($dados['reaproveitar_repertorio']) && !empty($dados['missa_origem_id'])
                    ? 'Missa criada com reaproveitamento de repertório anterior.'
                    : 'Missa criada para a rotina da igreja.',
            ]
        );

        return redirect()
            ->to(route('local-admin.missas.show', $missa) . '#missa-repertorio')
            ->with('success', !empty($dados['reaproveitar_repertorio']) && !empty($dados['missa_origem_id'])
                ? 'Missa cadastrada com sucesso. O repertório anterior foi copiado como ponto de partida.'
                : 'Missa cadastrada com sucesso. Agora adicione as músicas ao repertório.');
    }

    public function show(Missa $missa): View
    {
        $this->garantirMissaDaIgreja($missa);
        $igreja = $this->obterIgreja();
        $this->sincronizarMissasEncerradas($igreja);
        $missa->refresh();

        $missa->load([
            'tempoLiturgico',
            'celebrante',
            'missaMusicas' => fn ($query) => $query
                ->with([
                    'musica.tempoLiturgico',
                    'musica.momentoLiturgico',
                    'musica.versoesMusicais' => fn ($subQuery) => $subQuery->where('ativo', true)->orderBy('titulo'),
                    'versaoMusical',
                    'momentoLiturgico',
                    'solicitacoesMudancaTom.usuario',
                    'solicitacoesMudancaTom.revisor',
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
            'padres' => Usuario::query()
                ->where('eh_padre', true)
                ->where('ativo', true)
                ->orderBy('nome')
                ->get(),
        ]);
    }

    public function update(Request $request, Missa $missa): RedirectResponse
    {
        $this->garantirMissaDaIgreja($missa);
        $dados = $this->validarDadosMissa($request);
        $igreja = $this->obterIgreja();
        $usuario = $this->obterUsuario();

        DB::transaction(function () use ($dados, $missa): void {
            if (($dados['ativo'] ?? false) === true) {
                Missa::where('igreja_id', $missa->igreja_id)
                    ->where('id', '!=', $missa->id)
                    ->update(['ativo' => false]);
            }

            $missa->update([
                'celebrante_usuario_id' => $dados['padre_id'] ?? null,
                'tempo_liturgico_id' => $dados['tempo_liturgico_id'] ?? null,
                'titulo' => $dados['titulo'],
                'data_missa' => $dados['data_missa'],
                'hora_inicio' => $dados['hora_inicio'],
                'hora_fim' => $dados['hora_fim'],
                'observacoes' => $dados['observacoes'] ?? null,
                'publica_para_fieis' => (bool) ($dados['publica_para_fieis'] ?? false),
                'publica_para_musicos' => (bool) ($dados['publica_para_musicos'] ?? false),
                'ativo' => (bool) ($dados['ativo'] ?? false),
            ]);
        });

        $this->auditoriaOperacionalService->registrar(
            evento: 'missa_editada',
            ator: $usuario,
            igreja: $igreja,
            contexto: [
                'origem' => 'local_admin_missas_update',
                'origem_id' => $missa->id,
                'titulo' => $missa->titulo,
                'publica_para_fieis' => (bool) ($dados['publica_para_fieis'] ?? false),
                'publica_para_musicos' => (bool) ($dados['publica_para_musicos'] ?? false),
                'resumo' => 'Missa atualizada com ajustes de publicação e dados litúrgicos.',
            ]
        );

        return redirect()
            ->route('local-admin.missas.show', $missa)
            ->with('success', 'Missa atualizada com sucesso.');
    }

    public function destroy(Missa $missa): RedirectResponse
    {
        $this->garantirMissaDaIgreja($missa);

        return back()->withErrors([
            'missa' => 'A exclusão direta de missas não está disponível neste fluxo. Use a inativação para preservar o histórico da celebração.',
        ]);
    }

    public function toggle(Request $request, Missa $missa): RedirectResponse
    {
        $this->garantirMissaDaIgreja($missa);
        $igreja = $this->obterIgreja();
        $usuario = $this->obterUsuario();
        $novoStatus = !$missa->ativo;
        $dadosReativacao = $novoStatus ? $this->validarDadosReativacao($request, $missa) : [];

        DB::transaction(function () use ($missa, $novoStatus, $dadosReativacao): void {
            if ($novoStatus) {
                Missa::query()
                    ->where('igreja_id', $missa->igreja_id)
                    ->whereKeyNot($missa->id)
                    ->update(['ativo' => false]);
            }

            $missa->update(array_merge(['ativo' => $novoStatus], $dadosReativacao));
        });

        $this->auditoriaOperacionalService->registrar(
            evento: 'missa_editada',
            ator: $usuario,
            igreja: $igreja,
            contexto: [
                'origem' => 'local_admin_missas_toggle',
                'origem_id' => $missa->id,
                'titulo' => $missa->titulo,
                'ativo' => $novoStatus,
                'data_missa' => $dadosReativacao['data_missa'] ?? optional($missa->data_missa)->toDateString(),
                'hora_inicio' => $dadosReativacao['hora_inicio'] ?? $missa->hora_inicio,
                'hora_fim' => $dadosReativacao['hora_fim'] ?? $missa->hora_fim,
                'resumo' => $novoStatus
                    ? 'Missa reativada com nova data e horario no fluxo operacional da igreja.'
                    : 'Missa inativada para preservar o histórico sem excluir o repertório.',
            ]
        );

        return back()->with('success', $novoStatus
            ? 'Missa reativada com sucesso para ' . CarbonImmutable::parse($dadosReativacao['data_missa'])->format('d/m/Y') . ' as ' . $dadosReativacao['hora_inicio'] . '.'
            : 'Missa inativada com sucesso.');
    }

    public function concluirMontagem(Missa $missa): RedirectResponse
    {
        $this->garantirMissaDaIgreja($missa);

        $totalItens = $missa->missaMusicas()->count();

        if ($totalItens === 0) {
            return redirect()
                ->route('local-admin.missas.index')
                ->withErrors([
                    'missa' => 'Montagem nao concluida: adicione pelo menos uma musica ao repertorio de ' . $missa->titulo . '.',
                ]);
        }

        $itensSemVersao = $missa->missaMusicas()
            ->whereNull('versao_musical_id')
            ->count();

        if ($itensSemVersao > 0) {
            return redirect()
                ->to(route('local-admin.missas.show', $missa) . '#missa-repertorio')
                ->withErrors([
                    'missa' => 'Montagem ainda pendente: vincule uma versao/cifra nos ' . $itensSemVersao . ' item(ns) sem cifra antes de concluir.',
                ]);
        }

        return redirect()
            ->route('local-admin.missas.index')
            ->with('success', 'Montagem da missa "' . $missa->titulo . '" concluida com ' . $totalItens . ' item(ns) no repertorio.');
    }

    public function corrigirOrdemRepertorio(Missa $missa): RedirectResponse
    {
        $this->garantirMissaDaIgreja($missa);

        if ($missa->missaMusicas()->count() === 0) {
            return redirect()
                ->to(route('local-admin.missas.show', $missa) . '#missa-repertorio')
                ->withErrors([
                    'missa' => 'Adicione musicas ao repertorio antes de corrigir a ordem.',
                ]);
        }

        $this->reorganizarRepertorioPorMomento($missa);

        return redirect()
            ->to(route('local-admin.missas.show', $missa) . '#missa-repertorio')
            ->with('success', 'Ordem do repertorio corrigida pela sequencia dos momentos liturgicos.');
    }

    public function storeRepertorio(Request $request, Missa $missa): RedirectResponse
    {
        $this->garantirMissaDaIgreja($missa);
        $igreja = $this->obterIgreja();
        $usuario = $this->obterUsuario();

        $dados = $request->validate([
            'musica_id' => ['required', 'exists:musicas,id'],
            'versao_musical_id' => ['nullable', 'exists:versoes_musicais,id'],
            'tom_usado' => ['nullable', Rule::in(config('musical.tons', []))],
            'momento_liturgico_id' => ['nullable', Rule::exists('classificacoes_liturgicas', 'id')->where(fn ($query) => $query->where('tipo', 'momento'))],
        ], [
            'musica_id.required' => 'Selecione uma musica para adicionar ao repertorio.',
            'tom_usado.in' => 'Escolha um tom padronizado da lista para usar nesta missa.',
        ]);

        $musica = Musica::findOrFail($dados['musica_id']);

        if ($missa->missaMusicas()->where('musica_id', $musica->id)->exists()) {
            return back()
                ->withErrors([
                    'musica_id' => 'Esta musica ja esta no repertorio desta missa. Ajuste o item existente ou remova antes de adicionar novamente.',
                ])
                ->withInput();
        }

        if (!empty($dados['versao_musical_id'])) {
            $versao = VersaoMusical::findOrFail($dados['versao_musical_id']);
            if ((int) $versao->musica_id !== (int) $dados['musica_id']) {
                return back()->withErrors([
                    'versao_musical_id' => 'A versao musical selecionada nao pertence a musica escolhida.',
                ])->withInput();
            }
        }

        $versaoMusicalId = $dados['versao_musical_id']
            ?? $musica->versoesMusicais()
                ->where('ativo', true)
                ->orderBy('id')
                ->value('id');
        $proximaOrdem = (int) ($missa->missaMusicas()->max('ordem') ?? 0) + 1;

        $itemRepertorio = MissaMusica::create([
            'missa_id' => $missa->id,
            'musica_id' => $dados['musica_id'],
            'versao_musical_id' => $versaoMusicalId,
            'tom_usado' => $this->normalizarTomInformado($dados['tom_usado'] ?? null),
            'momento_liturgico_id' => $dados['momento_liturgico_id'] ?? $musica->momento_liturgico_id,
            'ordem' => $proximaOrdem,
        ]);

        $this->reorganizarRepertorioPorMomento($missa);
        $itemRepertorio->refresh();
        $itemRepertorio->loadMissing(['musica', 'versaoMusical', 'momentoLiturgico']);
        $this->auditoriaOperacionalService->registrar(
            evento: 'repertorio_item_adicionado',
            ator: $usuario,
            igreja: $igreja,
            contexto: [
                'origem' => 'local_admin_repertorio_store',
                'origem_id' => $itemRepertorio->id,
                'missa_id' => $missa->id,
                'missa_titulo' => $missa->titulo,
                'musica_id' => $itemRepertorio->musica_id,
                'titulo' => $itemRepertorio->musica?->titulo,
                'versao_id' => $itemRepertorio->versao_musical_id,
                'momento_liturgico_id' => $itemRepertorio->momento_liturgico_id,
                'resumo' => 'Música adicionada ao repertório da missa.',
            ]
        );

        return redirect()
            ->to(route('local-admin.missas.show', $missa) . '#missa-repertorio')
            ->with('success', 'Música adicionada ao repertório da missa.');
    }

    public function updateRepertorio(Request $request, Missa $missa, MissaMusica $missaMusica): RedirectResponse
    {
        $this->garantirMissaDaIgreja($missa);
        $this->garantirItemDaMissa($missa, $missaMusica);
        $igreja = $this->obterIgreja();
        $usuario = $this->obterUsuario();

        $dados = $request->validate([
            'versao_musical_id' => ['nullable', 'exists:versoes_musicais,id'],
            'tom_usado' => ['nullable', Rule::in(config('musical.tons', []))],
            'momento_liturgico_id' => ['nullable', Rule::exists('classificacoes_liturgicas', 'id')->where(fn ($query) => $query->where('tipo', 'momento'))],
        ], [
            'tom_usado.in' => 'Escolha um tom padronizado da lista para usar nesta missa.',
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

        $this->reorganizarRepertorioPorMomento($missa);
        $missaMusica->refresh();
        $missaMusica->loadMissing(['musica', 'versaoMusical', 'momentoLiturgico']);
        $this->auditoriaOperacionalService->registrar(
            evento: 'repertorio_item_atualizado',
            ator: $usuario,
            igreja: $igreja,
            contexto: [
                'origem' => 'local_admin_repertorio_update',
                'origem_id' => $missaMusica->id,
                'missa_id' => $missa->id,
                'missa_titulo' => $missa->titulo,
                'musica_id' => $missaMusica->musica_id,
                'titulo' => $missaMusica->musica?->titulo,
                'versao_id' => $missaMusica->versao_musical_id,
                'momento_liturgico_id' => $missaMusica->momento_liturgico_id,
                'resumo' => 'Item do repertório atualizado.',
            ]
        );

        return redirect()
            ->to(route('local-admin.missas.show', $missa) . '#repertorio-item-' . $missaMusica->id)
            ->with('success', 'Item do repertorio atualizado com sucesso.');
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
            return redirect()
                ->to(route('local-admin.missas.show', $missa) . '#repertorio-item-' . $missaMusica->id);
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

        return redirect()
            ->to(route('local-admin.missas.show', $missa) . '#repertorio-item-' . $missaMusica->id)
            ->with('success', 'Item movido para cima.');
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
            return redirect()
                ->to(route('local-admin.missas.show', $missa) . '#repertorio-item-' . $missaMusica->id);
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

        return redirect()
            ->to(route('local-admin.missas.show', $missa) . '#repertorio-item-' . $missaMusica->id)
            ->with('success', 'Item movido para baixo.');
    }

    public function destroyRepertorio(Missa $missa, MissaMusica $missaMusica): RedirectResponse
    {
        $this->garantirMissaDaIgreja($missa);
        $this->garantirItemDaMissa($missa, $missaMusica);
        $igreja = $this->obterIgreja();
        $usuario = $this->obterUsuario();
        $missaMusica->loadMissing(['musica', 'versaoMusical', 'momentoLiturgico']);

        $missaMusica->delete();

        $missa->missaMusicas()
            ->orderBy('ordem')
            ->get()
            ->values()
            ->each(function (MissaMusica $item, int $indice): void {
                $item->update(['ordem' => $indice + 1]);
            });

        $this->auditoriaOperacionalService->registrar(
            evento: 'repertorio_item_removido',
            ator: $usuario,
            igreja: $igreja,
            contexto: [
                'origem' => 'local_admin_repertorio_destroy',
                'origem_id' => $missaMusica->id,
                'missa_id' => $missa->id,
                'missa_titulo' => $missa->titulo,
                'musica_id' => $missaMusica->musica_id,
                'titulo' => $missaMusica->musica?->titulo,
                'versao_id' => $missaMusica->versao_musical_id,
                'resumo' => 'Item removido do repertório da missa.',
            ]
        );

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
            'celebrante',
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
            'celebrante',
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
            'tempo_liturgico_id' => ['nullable', Rule::exists('classificacoes_liturgicas', 'id')->where(fn ($query) => $query->where('tipo', 'tempo'))],
            'padre_id' => ['nullable', 'exists:usuarios,id'],
            'data_missa' => ['required', 'date', 'after_or_equal:' . $dataMinima, 'before_or_equal:' . $dataMaxima],
            'hora_inicio' => ['required', 'date_format:H:i'],
            'hora_fim' => ['required', 'date_format:H:i'],
            'observacoes' => ['nullable', 'string'],
            'publica_para_fieis' => ['nullable', 'boolean'],
            'publica_para_musicos' => ['nullable', 'boolean'],
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
            $celebranteId = $request->input('padre_id');

            if ($horaInicio !== '' && $horaFim !== '' && $horaInicio === $horaFim) {
                $validator->errors()->add(
                    'hora_fim',
                    'O horario de termino deve ser diferente do horario de inicio.'
                );
            }

            if (filled($celebranteId)) {
                $celebrante = Usuario::query()->find($celebranteId);

                if (!$celebrante?->eh_padre) {
                    $validator->errors()->add(
                        'padre_id',
                        'Selecione um celebrante valido.'
                    );
                }
            }
        });

        $dados = $validator->validate();

        if ($this->celebranteTemConflitoHorario(
            celebranteId: isset($dados['padre_id']) ? (int) $dados['padre_id'] : null,
            dataMissa: (string) $dados['data_missa'],
            horaInicio: (string) $dados['hora_inicio'],
            horaFim: (string) $dados['hora_fim'],
            ignorarMissaId: $request->route('missa')?->id
        )) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'padre_id' => 'Este celebrante ja esta vinculado a outra missa no mesmo horario.',
            ]);
        }

        return $dados;
    }

    private function validarDadosReativacao(Request $request, Missa $missa): array
    {
        $hoje = CarbonImmutable::now('America/Cuiaba')->startOfDay();
        $dataMaxima = $hoje->addMonth()->toDateString();

        $dados = $request->validate([
            'data_missa' => ['required', 'date', 'after_or_equal:' . $hoje->toDateString(), 'before_or_equal:' . $dataMaxima],
            'hora_inicio' => ['required', 'date_format:H:i'],
            'hora_fim' => ['required', 'date_format:H:i'],
        ], [
            'data_missa.required' => 'Informe a nova data para reativar a missa.',
            'data_missa.after_or_equal' => 'Para reativar, escolha hoje ou uma data futura.',
            'data_missa.before_or_equal' => 'A nova data nao pode ser posterior a 1 mes a frente.',
            'hora_inicio.required' => 'Informe o novo horario de inicio.',
            'hora_fim.required' => 'Informe o novo horario de termino.',
            'hora_inicio.date_format' => 'Informe o horario de inicio no formato HH:MM.',
            'hora_fim.date_format' => 'Informe o horario de termino no formato HH:MM.',
        ]);

        if ($dados['hora_inicio'] === $dados['hora_fim']) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'hora_fim' => 'O horario de termino deve ser diferente do horario de inicio.',
            ]);
        }

        if ($this->celebranteTemConflitoHorario(
            celebranteId: $missa->celebrante_usuario_id ? (int) $missa->celebrante_usuario_id : null,
            dataMissa: (string) $dados['data_missa'],
            horaInicio: (string) $dados['hora_inicio'],
            horaFim: (string) $dados['hora_fim'],
            ignorarMissaId: $missa->id
        )) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'hora_inicio' => 'O celebrante desta missa ja esta vinculado a outra missa no mesmo horario.',
            ]);
        }

        return $dados;
    }

    private function obterUsuario(): Usuario
    {
        /** @var \App\Models\Usuario $usuario */
        $usuario = Auth::user();

        abort_unless($usuario && $usuario->ehAdminLocal(), 403);

        return $usuario;
    }

    private function obterIgrejasAdministradas(Usuario $usuario, Igreja $igrejaAtiva): array
    {
        return $usuario->igrejasDisponiveisPorPapel(PapelIgreja::ADMIN_LOCAL)
            ->map(function (Igreja $igreja) use ($igrejaAtiva): Igreja {
                $igrejaComDados = clone $igreja;
                $igrejaComDados->setAttribute('eh_ativa', (int) $igreja->id === (int) $igrejaAtiva->id);

                return $igrejaComDados;
            })
            ->values()
            ->all();
    }

    private function obterIgreja(): Igreja
    {
        $igreja = $this->obterUsuario()->igrejaAtiva() ?? $this->obterUsuario()->igreja;

        abort_unless($igreja !== null, 404, 'Igreja nao encontrada para este administrador local.');
        abort_unless(
            $igreja->estaOperacional(),
            403,
            'Esta igreja ainda esta aguardando admin local ativo e nao pode operar missas, repertorios ou publicacoes.'
        );

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
        $linkPublicoMusicos = route('igrejas.public.musicos.show', ['slug' => $igreja->slugPublicoMusicos()]);

        $igreja->setAttribute('link_publico', $linkPublico);
        $igreja->setAttribute('link_publico_musicos', $linkPublicoMusicos);
        $igreja->setAttribute(
            'qr_code_url',
            'https://api.qrserver.com/v1/create-qr-code/?size=260x260&data=' . urlencode($linkPublico)
        );
        $igreja->setAttribute(
            'qr_code_url_musicos',
            'https://api.qrserver.com/v1/create-qr-code/?size=260x260&data=' . urlencode($linkPublicoMusicos)
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

    private function reorganizarRepertorioPorMomento(Missa $missa): void
    {
        $itens = $missa->missaMusicas()
            ->with('momentoLiturgico')
            ->orderBy('ordem')
            ->get();

        if ($itens->isEmpty()) {
            return;
        }

        $itensOrdenados = $itens
            ->sortBy([
                fn (MissaMusica $primeiro, MissaMusica $segundo): int => ($primeiro->momentoLiturgico?->ordem_exibicao ?? PHP_INT_MAX)
                    <=> ($segundo->momentoLiturgico?->ordem_exibicao ?? PHP_INT_MAX),
                fn (MissaMusica $primeiro, MissaMusica $segundo): int => $primeiro->ordem <=> $segundo->ordem,
                fn (MissaMusica $primeiro, MissaMusica $segundo): int => $primeiro->id <=> $segundo->id,
            ])
            ->values();

        DB::transaction(function () use ($itensOrdenados): void {
            $itensOrdenados->each(function (MissaMusica $item, int $indice): void {
                $item->update(['ordem' => -($indice + 1)]);
            });

            $itensOrdenados->each(function (MissaMusica $item, int $indice): void {
                $item->update(['ordem' => $indice + 1]);
            });
        });
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

    private function celebranteTemConflitoHorario(
        ?int $celebranteId,
        string $dataMissa,
        string $horaInicio,
        string $horaFim,
        ?int $ignorarMissaId = null
    ): bool {
        if ($celebranteId === null) {
            return false;
        }

        $missas = Missa::query()
            ->where('celebrante_usuario_id', $celebranteId)
            ->whereDate('data_missa', $dataMissa)
            ->when($ignorarMissaId, fn ($query) => $query->whereKeyNot($ignorarMissaId))
            ->get(['id', 'hora_inicio', 'hora_fim']);

        if ($missas->isEmpty()) {
            return false;
        }

        $novoInicio = CarbonImmutable::createFromFormat('H:i', $horaInicio, 'America/Cuiaba');
        $novoFim = CarbonImmutable::createFromFormat('H:i', $horaFim, 'America/Cuiaba');

        if ($novoFim->lessThanOrEqualTo($novoInicio)) {
            $novoFim = $novoFim->addDay();
        }

        foreach ($missas as $missaExistente) {
            $existenteInicio = CarbonImmutable::createFromFormat('H:i:s', substr((string) $missaExistente->hora_inicio, 0, 8), 'America/Cuiaba');
            $existenteFim = CarbonImmutable::createFromFormat('H:i:s', substr((string) $missaExistente->hora_fim, 0, 8), 'America/Cuiaba');

            if ($existenteFim->lessThanOrEqualTo($existenteInicio)) {
                $existenteFim = $existenteFim->addDay();
            }

            if ($novoInicio->lessThan($existenteFim) && $novoFim->greaterThan($existenteInicio)) {
                return true;
            }
        }

        return false;
    }
}
