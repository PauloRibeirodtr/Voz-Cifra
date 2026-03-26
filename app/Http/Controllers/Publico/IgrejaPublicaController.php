<?php

namespace App\Http\Controllers\Publico;

use App\Http\Controllers\Controller;
use App\Models\Igreja;
use App\Models\Missa;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
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
            'missa_id' => $missaPublica?->id,
            'countdown_iso' => $estado['countdownIso'],
            'timezone' => $timezone,
        ]);
    }

    private function montarEstadoPublico(Igreja $igreja, string $timezone): array
    {
        $agora = CarbonImmutable::now($timezone);

        Missa::query()
            ->where('igreja_id', $igreja->id)
            ->where('ativo', true)
            ->where(function ($query) use ($agora) {
                $query
                    ->whereDate('data_missa', '<', $agora->toDateString())
                    ->orWhere(function ($subQuery) use ($agora) {
                        $subQuery
                            ->whereDate('data_missa', $agora->toDateString())
                            ->where('hora_fim', '<', $agora->format('H:i:s'));
                    });
            })
            ->update(['ativo' => false]);

        $missaEmAndamento = Missa::query()
            ->with(['tempoLiturgico', 'padre'])
            ->where('igreja_id', $igreja->id)
            ->whereDate('data_missa', $agora->toDateString())
            ->where('hora_inicio', '<=', $agora->format('H:i:s'))
            ->where('hora_fim', '>=', $agora->format('H:i:s'))
            ->orderByRaw('case when ativo then 0 else 1 end')
            ->orderBy('hora_inicio')
            ->first();

        $proximaMissa = Missa::query()
            ->with(['tempoLiturgico', 'padre'])
            ->where('igreja_id', $igreja->id)
            ->where(function ($query) use ($agora) {
                $query
                    ->whereDate('data_missa', '>', $agora->toDateString())
                    ->orWhere(function ($subQuery) use ($agora) {
                        $subQuery
                            ->whereDate('data_missa', $agora->toDateString())
                            ->where('hora_inicio', '>', $agora->format('H:i:s'));
                    });
            })
            ->orderByRaw('case when ativo then 0 else 1 end')
            ->orderBy('data_missa')
            ->orderBy('hora_inicio')
            ->first();

        $missaPublica = $missaEmAndamento ?: $proximaMissa;
        $estadoCelebracao = $missaEmAndamento ? 'em_andamento' : ($proximaMissa ? 'proxima' : 'aguardando');
        $countdownIso = null;

        if ($missaPublica) {
            $horarioBase = $estadoCelebracao === 'em_andamento'
                ? substr((string) $missaPublica->hora_fim, 0, 8)
                : substr((string) $missaPublica->hora_inicio, 0, 8);

            $countdownIso = CarbonImmutable::createFromFormat(
                'Y-m-d H:i:s',
                $missaPublica->data_missa->format('Y-m-d') . ' ' . $horarioBase,
                $timezone
            )->toIso8601String();
        }

        return [
            'missaPublica' => $missaPublica,
            'estadoCelebracao' => $estadoCelebracao,
            'missaEmAndamento' => $missaEmAndamento,
            'proximaMissa' => $proximaMissa,
            'countdownIso' => $countdownIso,
        ];
    }
}
