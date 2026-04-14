<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Acorde;
use App\Models\Musica;
use App\Models\VersaoMusical;
use App\Rules\ValidChord;
use App\Services\NormalizadorCifrasService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class VersaoMusicalController extends Controller
{
    public function __construct(
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
        ]);
    }

    public function store(Request $request, Musica $musica): RedirectResponse
    {
        $dados = $request->validate([
            'titulo' => ['nullable', 'string', 'max:255'],
            'tom_musical' => ['nullable', 'string', 'max:50', new ValidChord()],
            'bpm' => ['nullable', 'integer', 'min:1', 'max:999'],
            'youtube_video_id' => ['nullable', 'string', 'max:255'],
            'letra_com_cifras' => ['required', 'string'],
            'ativo' => ['nullable', 'boolean'],
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
            'ativo' => (bool) ($dados['ativo'] ?? true),
        ]);

        $redirecionamento = redirect()
            ->route('admin.versoes-musicais.show', [$musica, $versaoMusical])
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
        ]);
    }

    public function update(Request $request, Musica $musica, VersaoMusical $versaoMusical): RedirectResponse
    {
        $this->garantirVinculoComMusica($musica, $versaoMusical);

        $dados = $request->validate([
            'titulo' => ['nullable', 'string', 'max:255'],
            'tom_musical' => ['nullable', 'string', 'max:50', new ValidChord()],
            'bpm' => ['nullable', 'integer', 'min:1', 'max:999'],
            'youtube_video_id' => ['nullable', 'string', 'max:255'],
            'letra_com_cifras' => ['required', 'string'],
            'ativo' => ['nullable', 'boolean'],
        ]);

        $acordesValidos = Acorde::where('ativo', true)->pluck('nome')->all();
        $resultadoCifras = $this->normalizadorCifrasService->processar($dados['letra_com_cifras'], $acordesValidos);

        $versaoMusical->update([
            'titulo' => $dados['titulo'] ?? null,
            'tom_musical' => $this->normalizarTomInformado($dados['tom_musical'] ?? null),
            'bpm' => $dados['bpm'] ?? null,
            'youtube_video_id' => $this->normalizarYoutubeVideoId($dados['youtube_video_id'] ?? null),
            'letra_com_cifras' => $resultadoCifras['texto_normalizado'],
            'ativo' => (bool) ($dados['ativo'] ?? false),
        ]);

        $redirecionamento = redirect()
            ->route('admin.versoes-musicais.show', [$musica, $versaoMusical])
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

        return redirect()
            ->route('admin.musicas.show', $musica)
            ->with('success', 'Versao musical inativada com sucesso.');
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

        return $tom !== '' ? $tom : null;
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
