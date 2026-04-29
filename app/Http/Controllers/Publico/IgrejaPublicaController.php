<?php

namespace App\Http\Controllers\Publico;

use App\Http\Controllers\Controller;
use App\Models\Acorde;
use App\Models\Igreja;
use App\Models\Missa;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\View\View;

class IgrejaPublicaController extends Controller
{
    public function show(Request $request, string $slug): View
    {
        return $this->renderizarPaginaPublica($request, $slug, 'fieis');
    }

    public function showMusicos(Request $request, string $slug): View
    {
        return $this->renderizarPaginaPublica($request, $slug, 'musicos');
    }

    public function status(Request $request, string $slug): JsonResponse
    {
        return $this->responderStatusPublico($request, $slug, 'fieis');
    }

    public function statusMusicos(Request $request, string $slug): JsonResponse
    {
        return $this->responderStatusPublico($request, $slug, 'musicos');
    }

    private function renderizarPaginaPublica(Request $request, string $slug, string $audiencia): View
    {
        $timezone = 'America/Cuiaba';
        $igreja = $this->buscarIgrejaPublica($slug, $audiencia);
        $estado = $this->montarEstadoPublico($request, $igreja, $timezone, $audiencia);
        $historicoBusca = trim((string) $request->input('historico', ''));
        $historicoMissas = $this->buscarHistorico($igreja, $timezone, $historicoBusca, $audiencia);

        return view('publico.igreja', [
            'igreja' => $igreja,
            'missaPublica' => $estado['missaPublica'],
            'estadoCelebracao' => $estado['estadoCelebracao'],
            'missaEmAndamento' => $estado['missaEmAndamento'],
            'proximaMissa' => $estado['proximaMissa'],
            'proximasMissas' => $estado['proximasMissas'],
            'missasHoje' => $estado['missasHoje'],
            'missasMusicos' => $estado['missasMusicos'],
            'celebracaoSelecionadaId' => $estado['celebracaoSelecionadaId'],
            'historicoMissas' => $historicoMissas,
            'historicoBusca' => $historicoBusca,
            'countdownIso' => $estado['countdownIso'],
            'timezoneExibicao' => $timezone,
            'modoPublico' => $audiencia,
            'bibliotecaAcordes' => $audiencia === 'musicos' ? $this->bibliotecaAcordesPublica() : [],
        ]);
    }

    private function responderStatusPublico(Request $request, string $slug, string $audiencia): JsonResponse
    {
        $timezone = 'America/Cuiaba';
        $igreja = $this->buscarIgrejaPublica($slug, $audiencia);
        $estado = $this->montarEstadoPublico($request, $igreja, $timezone, $audiencia);
        $missaPublica = $estado['missaPublica'];

        return response()->json([
            'estado' => $estado['estadoCelebracao'],
            'missa_ref' => $this->publicMissaReference($missaPublica ?: $estado['proximaMissa']),
            'countdown_iso' => $estado['countdownIso'],
            'timezone' => $timezone,
            'audiencia' => $audiencia,
        ]);
    }

    private function buscarIgrejaPublica(string $slug, string $audiencia): Igreja
    {
        $query = Igreja::query()
            ->where('ativo', true);

        if ($audiencia === 'musicos') {
            return $query
                ->where(function ($subquery) use ($slug): void {
                    $subquery
                        ->where('slug', $slug)
                        ->orWhere('slug_publico_musicos', $slug);
                })
                ->firstOrFail();
        }

        return $query
            ->where('slug', $slug)
            ->firstOrFail();
    }

    private function montarEstadoPublico(Request $request, Igreja $igreja, string $timezone, string $audiencia): array
    {
        $agora = CarbonImmutable::now($timezone);
        $hoje = $agora->toDateString();

        $missasOrdenadas = $this->queryMissasPublicas($igreja, $audiencia)
            ->with($this->publicMissaRelations())
            ->orderBy('data_missa')
            ->orderBy('hora_inicio')
            ->get();

        $missaEmAndamento = $missasOrdenadas
            ->first(fn (Missa $missa): bool => $this->missaEstaEmAndamento($missa, $agora, $timezone));

        $proximasMissasColecao = $missasOrdenadas
            ->filter(fn (Missa $missa): bool => $missa->dataHoraInicio($timezone)->greaterThan($agora)
                && $missa->dataHoraInicio($timezone)->lessThanOrEqualTo($agora->addMonth()))
            ->values();

        $missasHojeColecao = $missasOrdenadas
            ->filter(fn (Missa $missa): bool => $missa->dataHoraInicio($timezone)->toDateString() === $hoje)
            ->values();

        $missasMusicosColecao = $missasOrdenadas
            ->filter(fn (Missa $missa): bool => $missa->dataHoraFim($timezone)->greaterThanOrEqualTo($agora))
            ->values();

        $proximaMissa = $proximasMissasColecao->first();
        $celebracaoSelecionadaId = max(0, (int) $request->integer('celebracao'));
        $missaPublica = $audiencia === 'musicos'
            ? $this->resolverMissaPublicaMusicos($missasMusicosColecao, $celebracaoSelecionadaId, $missaEmAndamento, $proximaMissa)
            : $this->resolverMissaPublicaFieis($missasHojeColecao, $celebracaoSelecionadaId);

        $this->anexarRepertorioPublico($missaPublica, $audiencia === 'musicos');

        $estadoCelebracao = $audiencia === 'musicos'
            ? ($missaEmAndamento ? 'em_andamento' : ($proximaMissa ? 'proxima' : 'aguardando'))
            : ($missasHojeColecao->isNotEmpty() ? 'programacao_hoje' : 'aguardando');
        $countdownReferencia = $audiencia === 'musicos'
            ? ($missaEmAndamento ? $missaEmAndamento->dataHoraFim($timezone) : $proximaMissa?->dataHoraInicio($timezone))
            : ($missasHojeColecao->last()?->dataHoraFim($timezone) ?: $proximaMissa?->dataHoraInicio($timezone));

        return [
            'missaPublica' => $missaPublica,
            'estadoCelebracao' => $estadoCelebracao,
            'missaEmAndamento' => $missaEmAndamento,
            'proximaMissa' => $proximaMissa,
            'missasHoje' => $missasHojeColecao
                ->map(fn (Missa $missa) => $this->mapearMissaDoDia(
                    $missa,
                    $timezone,
                    (int) $missa->id === (int) ($missaPublica?->id ?? 0)
                ))
                ->values(),
            'missasMusicos' => $missasMusicosColecao
                ->map(fn (Missa $missa) => $this->mapearMissaMusico(
                    $missa,
                    $timezone,
                    (int) $missa->id === (int) ($missaPublica?->id ?? 0),
                    $this->missaEstaEmAndamento($missa, $agora, $timezone)
                ))
                ->values(),
            'celebracaoSelecionadaId' => (int) ($missaPublica?->id ?? 0),
            'proximasMissas' => $proximasMissasColecao
                ->map(fn (Missa $missa) => $this->mapearAgendaMissa($missa, $timezone))
                ->values(),
            'countdownIso' => $countdownReferencia?->toIso8601String(),
        ];
    }

    private function resolverMissaPublicaFieis(Collection $missasHoje, int $celebracaoSelecionadaId): ?Missa
    {
        if ($missasHoje->isEmpty()) {
            return null;
        }

        if ($celebracaoSelecionadaId > 0) {
            $missaSelecionada = $missasHoje->firstWhere('id', $celebracaoSelecionadaId);

            if ($missaSelecionada instanceof Missa) {
                return $missaSelecionada;
            }
        }

        $primeiraMissa = $missasHoje->first();

        return $primeiraMissa instanceof Missa ? $primeiraMissa : null;
    }

    private function resolverMissaPublicaMusicos(
        Collection $missasMusicos,
        int $celebracaoSelecionadaId,
        ?Missa $missaEmAndamento,
        ?Missa $proximaMissa
    ): ?Missa {
        if ($missasMusicos->isEmpty()) {
            return null;
        }

        if ($celebracaoSelecionadaId > 0) {
            $missaSelecionada = $missasMusicos->firstWhere('id', $celebracaoSelecionadaId);

            if ($missaSelecionada instanceof Missa) {
                return $missaSelecionada;
            }
        }

        if ($missaEmAndamento instanceof Missa) {
            return $missaEmAndamento;
        }

        if ($proximaMissa instanceof Missa) {
            return $proximaMissa;
        }

        $primeiraMissa = $missasMusicos->first();

        return $primeiraMissa instanceof Missa ? $primeiraMissa : null;
    }

    private function buscarHistorico(Igreja $igreja, string $timezone, string $busca, string $audiencia)
    {
        $agora = CarbonImmutable::now($timezone);

        return $this->queryMissasPublicas($igreja, $audiencia)
            ->with(['tempoLiturgico'])
            ->orderByDesc('data_missa')
            ->orderByDesc('hora_inicio')
            ->get()
            ->filter(fn (Missa $missa): bool => $missa->dataHoraFim($timezone)->lessThan($agora))
            ->map(fn (Missa $missa) => $this->mapearHistoricoMissa($missa, $timezone))
            ->filter(function (array $missa) use ($busca): bool {
                if ($busca === '') {
                    return true;
                }

                $buscaNormalizada = Str::lower(Str::ascii($busca));
                $conteudo = Str::lower(Str::ascii(implode(' ', [
                    $missa['titulo'],
                    $missa['data'],
                    $missa['dia_semana'],
                    $missa['horario'],
                    $missa['tempo_liturgico'] ?? '',
                ])));

                return str_contains($conteudo, $buscaNormalizada);
            })
            ->take(12)
            ->values();
    }

    private function queryMissasPublicas(Igreja $igreja, string $audiencia)
    {
        return Missa::query()
            ->where('igreja_id', $igreja->id)
            ->where('ativo', true)
            ->where($this->colunaPublicacaoPorAudiencia($audiencia), true);
    }

    private function colunaPublicacaoPorAudiencia(string $audiencia): string
    {
        return $audiencia === 'musicos' ? 'publica_para_musicos' : 'publica_para_fieis';
    }

    private function missaEstaEmAndamento(Missa $missa, CarbonImmutable $agora, string $timezone): bool
    {
        return $missa->dataHoraInicio($timezone)->lessThanOrEqualTo($agora)
            && $missa->dataHoraFim($timezone)->greaterThanOrEqualTo($agora);
    }

    private function publicMissaRelations(): array
    {
        return [
            'tempoLiturgico',
            'padre',
            'missaMusicas' => fn ($query) => $query
                ->with(['musica', 'versaoMusical', 'momentoLiturgico'])
                ->orderBy('ordem'),
        ];
    }

    private function anexarRepertorioPublico(?Missa $missa, bool $exibirCifras = false): void
    {
        if (!$missa) {
            return;
        }

        $itens = $missa->missaMusicas->map(function ($item) use ($exibirCifras) {
            $letraBase = $item->versaoMusical?->letra_com_cifras ?: $item->musica?->letra ?: '';

            return [
                'ordem' => $item->ordem,
                'titulo' => $item->musica?->titulo ?: 'Canto sem titulo',
                'momento' => $item->momentoLiturgico?->nome,
                'tom' => $item->tomExibicao,
                'letra_publica' => $exibirCifras
                    ? $this->normalizarLetraMusico($letraBase)
                    : $this->limparLetraPublica($letraBase),
                'letra_publica_html' => $exibirCifras
                    ? $this->formatarLetraMusicoParaHtml($letraBase)
                    : null,
            ];
        })->values();

        $missa->setAttribute('itens_publicos', $itens);
    }

    private function limparLetraPublica(string $texto): string
    {
        $textoSemCifras = preg_replace('/\[[^\]]+\]/', '', $texto) ?? $texto;
        $textoSemEspacosExtras = preg_replace("/[ \t]+\n/", "\n", $textoSemCifras) ?? $textoSemCifras;
        $textoNormalizado = preg_replace("/\n{3,}/", "\n\n", trim($textoSemEspacosExtras)) ?? trim($textoSemEspacosExtras);

        return $textoNormalizado;
    }

    private function normalizarLetraMusico(string $texto): string
    {
        return preg_replace("/\n{3,}/", "\n\n", trim($texto)) ?? trim($texto);
    }

    private function formatarLetraMusicoParaHtml(string $texto): string
    {
        $textoNormalizado = $this->normalizarLetraMusico($texto);
        $textoEscapado = e($textoNormalizado);
        $textoComCifras = preg_replace('/\[(.*?)\]/', '<span class="chord-mark">[$1]</span>', $textoEscapado) ?? $textoEscapado;

        return nl2br($textoComCifras, false);
    }

    private function mapearAgendaMissa(Missa $missa, string $timezone): array
    {
        $inicio = $missa->dataHoraInicio($timezone);

        return [
            'titulo' => $missa->titulo,
            'data' => $inicio->format('d/m'),
            'dia_semana' => mb_convert_case($inicio->locale('pt_BR')->isoFormat('dddd'), MB_CASE_TITLE, 'UTF-8'),
            'horario' => $inicio->format('H:i'),
            'tempo_liturgico' => $missa->tempoLiturgico?->nome,
        ];
    }

    private function mapearMissaDoDia(Missa $missa, string $timezone, bool $selecionada = false): array
    {
        $inicio = $missa->dataHoraInicio($timezone);

        return [
            'id' => $missa->id,
            'titulo' => $missa->titulo,
            'data' => $inicio->format('d/m/Y'),
            'dia_semana' => mb_convert_case($inicio->locale('pt_BR')->isoFormat('dddd'), MB_CASE_TITLE, 'UTF-8'),
            'horario' => $inicio->format('H:i'),
            'tempo_liturgico' => $missa->tempoLiturgico?->nome,
            'selecionada' => $selecionada,
        ];
    }

    private function mapearMissaMusico(
        Missa $missa,
        string $timezone,
        bool $selecionada = false,
        bool $emAndamento = false
    ): array {
        $inicio = $missa->dataHoraInicio($timezone);

        return [
            'id' => $missa->id,
            'titulo' => $missa->titulo,
            'data' => $inicio->format('d/m/Y'),
            'dia_semana' => mb_convert_case($inicio->locale('pt_BR')->isoFormat('dddd'), MB_CASE_TITLE, 'UTF-8'),
            'horario' => $inicio->format('H:i'),
            'tempo_liturgico' => $missa->tempoLiturgico?->nome,
            'selecionada' => $selecionada,
            'em_andamento' => $emAndamento,
        ];
    }

    private function mapearHistoricoMissa(Missa $missa, string $timezone): array
    {
        $inicio = $missa->dataHoraInicio($timezone);

        return [
            'titulo' => $missa->titulo,
            'data' => $inicio->format('d/m/Y'),
            'dia_semana' => mb_convert_case($inicio->locale('pt_BR')->isoFormat('dddd'), MB_CASE_TITLE, 'UTF-8'),
            'horario' => $inicio->format('H:i'),
            'tempo_liturgico' => $missa->tempoLiturgico?->nome,
        ];
    }

    private function publicMissaReference(?Missa $missa): ?string
    {
        if (!$missa) {
            return null;
        }

        return hash_hmac('sha256', implode('|', [
            $missa->id,
            $missa->dataHoraInicio('America/Cuiaba')->format('Y-m-d H:i:s'),
            $missa->dataHoraFim('America/Cuiaba')->format('Y-m-d H:i:s'),
        ]), (string) Config::get('app.key'));
    }

    private function bibliotecaAcordesPublica(): array
    {
        return Acorde::query()
            ->where('ativo', true)
            ->orderBy('nome')
            ->get()
            ->map(function (Acorde $acorde): array {
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
            })
            ->values()
            ->all();
    }
}
