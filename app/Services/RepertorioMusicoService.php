<?php

namespace App\Services;

use App\Models\Missa;
use App\Models\Usuario;
use App\Models\VersaoMusical;
use Carbon\CarbonImmutable;

class RepertorioMusicoService
{
    public function obterMissaDisponivelParaUsuario(Usuario $usuario): ?Missa
    {
        $igrejaId = $usuario->igrejaAtivaId() ?? $usuario->igreja_id;

        if (!$igrejaId) {
            return null;
        }

        $agora = CarbonImmutable::now('America/Cuiaba');

        $missas = Missa::query()
            ->with([
                'tempoLiturgico',
                'missaMusicas' => fn ($query) => $query
                    ->with(['musica', 'versaoMusical', 'momentoLiturgico', 'solicitacoesMudancaTom'])
                    ->orderBy('ordem'),
            ])
            ->where('igreja_id', $igrejaId)
            ->where('publica_para_musicos', true)
            ->whereHas('missaMusicas')
            ->orderByDesc('data_missa')
            ->orderByDesc('hora_inicio')
            ->limit(30)
            ->get();

        /** @var Missa|null $missa */
        $missa = $missas
            ->sortBy(function (Missa $missa) use ($agora): array {
                $fim = $missa->dataHoraFim('America/Cuiaba');
                $inicio = $missa->dataHoraInicio('America/Cuiaba');

                if ($missa->ativo && $fim->greaterThanOrEqualTo($agora)) {
                    return [0, $inicio->getTimestamp()];
                }

                if ($fim->greaterThanOrEqualTo($agora)) {
                    return [1, $inicio->getTimestamp()];
                }

                return [2, -$fim->getTimestamp()];
            })
            ->first();

        return $missa;
    }

    public function obterMissaComVersaoParaUsuario(Usuario $usuario, VersaoMusical $versaoMusical): ?Missa
    {
        $igrejaId = $usuario->igrejaAtivaId() ?? $usuario->igreja_id;

        if (!$igrejaId) {
            return null;
        }

        $agora = CarbonImmutable::now('America/Cuiaba');

        $missas = Missa::query()
            ->with([
                'missaMusicas' => fn ($query) => $query
                    ->where('versao_musical_id', $versaoMusical->id)
                    ->with('solicitacoesMudancaTom')
                    ->orderBy('ordem'),
            ])
            ->where('igreja_id', $igrejaId)
            ->where('publica_para_musicos', true)
            ->whereHas('missaMusicas', fn ($query) => $query->where('versao_musical_id', $versaoMusical->id))
            ->orderByDesc('data_missa')
            ->orderByDesc('hora_inicio')
            ->limit(30)
            ->get();

        /** @var Missa|null $missa */
        $missa = $missas
            ->sortBy(function (Missa $missa) use ($agora): array {
                $fim = $missa->dataHoraFim('America/Cuiaba');
                $inicio = $missa->dataHoraInicio('America/Cuiaba');

                if ($missa->ativo && $fim->greaterThanOrEqualTo($agora)) {
                    return [0, $inicio->getTimestamp()];
                }

                if ($fim->greaterThanOrEqualTo($agora)) {
                    return [1, $inicio->getTimestamp()];
                }

                return [2, -$fim->getTimestamp()];
            })
            ->first();

        return $missa;
    }
}
