<?php

namespace App\Http\Controllers\Publico;

use App\Http\Controllers\Controller;
use App\Models\Igreja;
use App\Models\Missa;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Config;
use Illuminate\View\View;

class IgrejaPublicaController extends Controller
{
    public function show(string $slug): View
    {
        $timezone = 'America/Cuiaba';
        $igreja = Igreja::query()
            ->where('slug', $slug)
            ->where('ativo', true)
            ->firstOrFail();

        $estado = $this->montarEstadoPublico($igreja, $timezone);

        return view('publico.igreja', [
            'igreja' => $igreja,
            'missaPublica' => $estado['missaPublica'],
            'estadoCelebracao' => $estado['estadoCelebracao'],
            'missaEmAndamento' => $estado['missaEmAndamento'],
            'proximaMissa' => $estado['proximaMissa'],
            'countdownIso' => $estado['countdownIso'],
            'timezoneExibicao' => $timezone,
        ]);
    }

    public function status(string $slug): JsonResponse
    {
        $timezone = 'America/Cuiaba';
        $igreja = Igreja::query()
            ->where('slug', $slug)
            ->where('ativo', true)
            ->firstOrFail();

        $estado = $this->montarEstadoPublico($igreja, $timezone);
        $missaPublica = $estado['missaPublica'];

        return response()->json([
            'estado' => $estado['estadoCelebracao'],
            'missa_ref' => $this->publicMissaReference($missaPublica),
            'countdown_iso' => $estado['countdownIso'],
            'timezone' => $timezone,
        ]);
    }

    private function montarEstadoPublico(Igreja $igreja, string $timezone): array
    {
        $agora = CarbonImmutable::now($timezone);
        $idsEncerrados = Missa::query()
            ->where('igreja_id', $igreja->id)
            ->where('ativo', true)
            ->get()
            ->filter(fn (Missa $missa): bool => $missa->dataHoraFim($timezone)->lessThan($agora))
            ->pluck('id');

        if ($idsEncerrados->isNotEmpty()) {
            Missa::query()
                ->whereKey($idsEncerrados)
                ->update(['ativo' => false]);
        }

        $missasOrdenadas = Missa::query()
            ->with($this->publicMissaRelations())
            ->where('igreja_id', $igreja->id)
            ->orderByRaw('case when ativo then 0 else 1 end')
            ->orderBy('data_missa')
            ->orderBy('hora_inicio')
            ->get();

        $missaEmAndamento = $missasOrdenadas
            ->first(fn (Missa $missa): bool => $this->missaEstaEmAndamento($missa, $agora, $timezone));

        $proximaMissa = $missasOrdenadas
            ->first(fn (Missa $missa): bool => $missa->dataHoraInicio($timezone)->greaterThan($agora));

        $missaPublica = $missaEmAndamento ?: $proximaMissa;
        $this->anexarRepertorioPublico($missaPublica);
        $estadoCelebracao = $missaEmAndamento ? 'em_andamento' : ($proximaMissa ? 'proxima' : 'aguardando');
        $countdownIso = null;

        if ($missaPublica) {
            $countdownIso = ($estadoCelebracao === 'em_andamento'
                ? $missaPublica->dataHoraFim($timezone)
                : $missaPublica->dataHoraInicio($timezone))
                ->toIso8601String();
        }

        return [
            'missaPublica' => $missaPublica,
            'estadoCelebracao' => $estadoCelebracao,
            'missaEmAndamento' => $missaEmAndamento,
            'proximaMissa' => $proximaMissa,
            'countdownIso' => $countdownIso,
        ];
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

    private function anexarRepertorioPublico(?Missa $missa): void
    {
        if (!$missa) {
            return;
        }

        $itens = $missa->missaMusicas->map(function ($item) {
            $letraBase = $item->versaoMusical?->letra_com_cifras ?: $item->musica?->letra ?: '';

            return [
                'ordem' => $item->ordem,
                'titulo' => $item->musica?->titulo ?: 'Canto sem titulo',
                'momento' => $item->momentoLiturgico?->nome,
                'letra_publica' => $this->limparLetraPublica($letraBase),
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
}
