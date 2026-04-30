<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Acorde;
use App\Models\Musica;
use App\Models\VersaoMusical;
use App\Services\AuditoriaOperacionalService;
use App\Services\NotificacaoSistemaService;
use App\Services\NormalizadorCifrasService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class VersaoMusicalController extends Controller
{
    public function __construct(
        private readonly AuditoriaOperacionalService $auditoriaOperacionalService,
        private readonly NotificacaoSistemaService $notificacaoSistemaService,
        private readonly NormalizadorCifrasService $normalizadorCifrasService
    ) {
    }

    public function create(Musica $musica): View
    {
        $acordes = Acorde::where('ativo', true)->orderBy('nome')->get();

        return view('admin.versoes-musicais.create', [
            'musica' => $musica,
            'acordes' => $acordes,
            'acordesValidos' => $acordes->pluck('nome')->values()->all(),
            'tonsMusicais' => config('musical.tons', []),
        ]);
    }

    public function store(Request $request, Musica $musica): RedirectResponse
    {
        $dados = $request->validate([
            'titulo' => ['nullable', 'string', 'max:255'],
            'tom_musical' => ['nullable', Rule::in(config('musical.tons', []))],
            'bpm' => ['nullable', 'integer', 'min:1', 'max:999'],
            'youtube_video_id' => ['nullable', 'string', 'max:255'],
            'letra_com_cifras' => ['required', 'string'],
            'ativo' => ['nullable', 'boolean'],
        ], [
            'tom_musical.in' => 'Escolha um tom musical padronizado da lista.',
        ]);

        $acordesValidos = Acorde::where('ativo', true)->pluck('nome')->all();
        $resultadoCifras = $this->normalizadorCifrasService->processar($dados['letra_com_cifras'], $acordesValidos);

        /** @var \App\Models\Usuario $usuario */
        $usuario = Auth::user();

        $versaoMusical = VersaoMusical::create([
            'musica_id' => $musica->id,
            'titulo' => $dados['titulo'] ?? null,
            'tom_musical' => $this->normalizarTomInformado($dados['tom_musical'] ?? null),
            'bpm' => $dados['bpm'] ?? null,
            'youtube_video_id' => $this->normalizarYoutubeVideoId($dados['youtube_video_id'] ?? null),
            'letra_com_cifras' => $resultadoCifras['texto_normalizado'],
            'criado_por' => $usuario->id,
            'ativo' => $this->podeInativarRegistros() ? (bool) ($dados['ativo'] ?? true) : true,
        ]);

        $this->auditoriaOperacionalService->registrar(
            evento: 'versao_musical_criada',
            ator: $usuario,
            igreja: null,
            contexto: [
                'origem' => 'admin_versoes_musicais_store',
                'origem_id' => $versaoMusical->id,
                'musica_id' => $musica->id,
                'musica_titulo' => $musica->titulo,
                'tom_musical' => $versaoMusical->tom_musical,
                'resumo' => 'Versao musical criada com cifras para a musica base.',
            ]
        );

        $this->notificacaoSistemaService->enviarParaUsuariosOperacionaisAtivos(
            evento: 'versao_musical_criada',
            ator: $usuario,
            contexto: [
                'origem' => 'admin_versoes_musicais_store',
                'origem_id' => $versaoMusical->id,
                'titulo' => $musica->titulo,
                'nome' => $versaoMusical->titulo ?: 'Versão principal',
            ]
        );

        $redirecionamento = redirect()
            ->route($this->routeName('musicas.show'), $musica)
            ->with('success', 'Versao musical cadastrada com sucesso.');

        if ($resultadoCifras['houve_conversao']) {
            $redirecionamento->with('info', 'O texto foi convertido automaticamente para o formato interno com colchetes.');
        }

        if ($resultadoCifras['acordes_invalidos'] !== []) {
            $redirecionamento->with('warning', 'Alguns acordes nao foram encontrados na biblioteca: ' . implode(', ', $resultadoCifras['acordes_invalidos']) . '.');
        }

        return $redirecionamento;
    }

    public function show(Musica $musica, VersaoMusical $versaoMusical): View
    {
        $this->garantirVinculoComMusica($musica, $versaoMusical);

        $versaoMusical->load('criadoPor');
        $acordesAtivos = Acorde::where('ativo', true)->orderBy('nome')->get();
        $resultadoCifras = $this->normalizadorCifrasService->processar(
            $versaoMusical->letra_com_cifras,
            $acordesAtivos->pluck('nome')->all()
        );

        $acordesDaVersao = $acordesAtivos
            ->whereIn('nome', $resultadoCifras['acordes_encontrados'])
            ->sortBy('nome')
            ->values()
            ->map(fn (Acorde $acorde): array => $this->adaptarAcordeParaPreview($acorde))
            ->values()
            ->all();

        $bibliotecaAcordes = $acordesAtivos
            ->map(fn (Acorde $acorde): array => $this->adaptarAcordeParaPreview($acorde))
            ->values()
            ->all();

        return view('admin.versoes-musicais.show', [
            'musica' => $musica,
            'versaoMusical' => $versaoMusical,
            'letraSemCifras' => $resultadoCifras['texto_sem_cifras'],
            'acordesEncontrados' => $resultadoCifras['acordes_encontrados'],
            'acordesInvalidos' => $resultadoCifras['acordes_invalidos'],
            'acordesDaVersao' => $acordesDaVersao,
            'bibliotecaAcordes' => $bibliotecaAcordes,
        ]);
    }

    public function edit(Musica $musica, VersaoMusical $versaoMusical): View
    {
        $this->garantirVinculoComMusica($musica, $versaoMusical);
        $acordes = Acorde::where('ativo', true)->orderBy('nome')->get();

        return view('admin.versoes-musicais.edit', [
            'musica' => $musica,
            'versaoMusical' => $versaoMusical,
            'acordes' => $acordes,
            'acordesValidos' => $acordes->pluck('nome')->values()->all(),
            'tonsMusicais' => config('musical.tons', []),
        ]);
    }

    public function update(Request $request, Musica $musica, VersaoMusical $versaoMusical): RedirectResponse
    {
        $this->garantirVinculoComMusica($musica, $versaoMusical);

        $dados = $request->validate([
            'titulo' => ['nullable', 'string', 'max:255'],
            'tom_musical' => ['nullable', Rule::in(config('musical.tons', []))],
            'bpm' => ['nullable', 'integer', 'min:1', 'max:999'],
            'youtube_video_id' => ['nullable', 'string', 'max:255'],
            'letra_com_cifras' => ['required', 'string'],
            'ativo' => ['nullable', 'boolean'],
        ], [
            'tom_musical.in' => 'Escolha um tom musical padronizado da lista.',
        ]);

        $acordesValidos = Acorde::where('ativo', true)->pluck('nome')->all();
        $resultadoCifras = $this->normalizadorCifrasService->processar($dados['letra_com_cifras'], $acordesValidos);
        /** @var \App\Models\Usuario|null $usuario */
        $usuario = Auth::user();

        $versaoMusical->update([
            'titulo' => $dados['titulo'] ?? null,
            'tom_musical' => $this->normalizarTomInformado($dados['tom_musical'] ?? null),
            'bpm' => $dados['bpm'] ?? null,
            'youtube_video_id' => $this->normalizarYoutubeVideoId($dados['youtube_video_id'] ?? null),
            'letra_com_cifras' => $resultadoCifras['texto_normalizado'],
            'ativo' => $this->podeInativarRegistros() ? (bool) ($dados['ativo'] ?? false) : (bool) $versaoMusical->ativo,
        ]);

        $this->auditoriaOperacionalService->registrar(
            evento: 'versao_musical_editada',
            ator: $usuario,
            igreja: null,
            contexto: [
                'origem' => 'admin_versoes_musicais_update',
                'origem_id' => $versaoMusical->id,
                'musica_id' => $musica->id,
                'musica_titulo' => $musica->titulo,
                'tom_musical' => $versaoMusical->tom_musical,
                'resumo' => 'Versao musical atualizada na biblioteca.',
            ]
        );

        $redirecionamento = redirect()
            ->route($this->routeName('musicas.show'), $musica)
            ->with('success', 'Versao musical atualizada com sucesso.');

        if ($resultadoCifras['houve_conversao']) {
            $redirecionamento->with('info', 'O texto foi convertido automaticamente para o formato interno com colchetes.');
        }

        if ($resultadoCifras['acordes_invalidos'] !== []) {
            $redirecionamento->with('warning', 'Alguns acordes nao foram encontrados na biblioteca: ' . implode(', ', $resultadoCifras['acordes_invalidos']) . '.');
        }

        return $redirecionamento;
    }

    public function destroy(Musica $musica, VersaoMusical $versaoMusical): RedirectResponse
    {
        $this->garantirVinculoComMusica($musica, $versaoMusical);

        $versaoMusical->update([
            'ativo' => false,
        ]);

        /** @var \App\Models\Usuario|null $usuario */
        $usuario = Auth::user();
        $this->auditoriaOperacionalService->registrar(
            evento: 'versao_musical_inativada',
            ator: $usuario,
            igreja: null,
            contexto: [
                'origem' => 'admin_versoes_musicais_destroy',
                'origem_id' => $versaoMusical->id,
                'musica_id' => $musica->id,
                'musica_titulo' => $musica->titulo,
                'resumo' => 'Versao musical marcada como inativa.',
            ]
        );

        return redirect()
            ->route($this->routeName('musicas.show'), $musica)
            ->with('success', 'Versao musical inativada com sucesso.');
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

    private function garantirVinculoComMusica(Musica $musica, VersaoMusical $versaoMusical): void
    {
        abort_unless($versaoMusical->musica_id === $musica->id, 404);
    }

    private function normalizarYoutubeVideoId(?string $valor): ?string
    {
        if ($valor === null) {
            return null;
        }

        $valor = trim($valor);

        if ($valor === '') {
            return null;
        }

        if (preg_match('/(?:v=|youtu\.be\/)([A-Za-z0-9_-]{6,})/', $valor, $matches) === 1) {
            return $matches[1];
        }

        return $valor;
    }

    private function normalizarTomInformado(?string $tom): ?string
    {
        $tom = trim((string) $tom);

        if ($tom === '') {
            return null;
        }

        $tonsDisponiveis = config('musical.tons', []);

        foreach ($tonsDisponiveis as $tomDisponivel) {
            if (strcasecmp((string) $tomDisponivel, $tom) === 0) {
                return (string) $tomDisponivel;
            }
        }

        return $tom;
    }

    private function adaptarAcordeParaPreview(Acorde $acorde): array
    {
        $shape = $acorde->dados_diagrama;

        if (is_string($shape) && $shape !== '') {
            $decodificado = json_decode($shape, true);
            $shape = json_last_error() === JSON_ERROR_NONE ? $decodificado : null;
        }

        return [
            'id' => $acorde->id,
            'nome' => $acorde->nome,
            'descricao' => $acorde->descricao,
            'shape' => is_array($shape) ? $shape : null,
        ];
    }
}
