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

        $hoje = CarbonImmutable::now('America/Cuiaba')->toDateString();

        /** @var Missa|null $missa */
        $missa = Missa::query()
            ->with([
                'tempoLiturgico',
                'missaMusicas' => fn ($query) => $query
                    ->with(['musica', 'versaoMusical', 'momentoLiturgico'])
                    ->orderBy('ordem'),
            ])
            ->where('igreja_id', $igrejaId)
            ->whereHas('missaMusicas')
            ->where(function ($query) use ($hoje): void {
                $query->where('ativo', true)
                    ->orWhereDate('data_missa', '>=', $hoje);
            })
            ->orderByRaw('case when ativo then 0 else 1 end')
            ->orderBy('data_missa')
            ->orderBy('hora_inicio')
            ->first();

        return $missa;
    }

    public function obterMissaComVersaoParaUsuario(Usuario $usuario, VersaoMusical $versaoMusical): ?Missa
    {
        $igrejaId = $usuario->igrejaAtivaId() ?? $usuario->igreja_id;

        if (!$igrejaId) {
            return null;
        }

        $hoje = CarbonImmutable::now('America/Cuiaba')->toDateString();

        /** @var Missa|null $missa */
        $missa = Missa::query()
            ->with([
                'missaMusicas' => fn ($query) => $query
                    ->where('versao_musical_id', $versaoMusical->id)
                    ->orderBy('ordem'),
            ])
            ->where('igreja_id', $igrejaId)
            ->whereHas('missaMusicas', fn ($query) => $query->where('versao_musical_id', $versaoMusical->id))
            ->where(function ($query) use ($hoje): void {
                $query->where('ativo', true)
                    ->orWhereDate('data_missa', '>=', $hoje);
            })
            ->orderByRaw('case when ativo then 0 else 1 end')
            ->orderBy('data_missa')
            ->orderBy('hora_inicio')
            ->first();

        return $missa;
    }
}
