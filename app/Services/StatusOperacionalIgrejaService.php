<?php

namespace App\Services;

use App\Enums\PapelIgreja;
use App\Models\Igreja;
use App\Models\Usuario;
use Illuminate\Support\Facades\DB;

class StatusOperacionalIgrejaService
{
    public function atualizar(Igreja|int $igreja): Igreja
    {
        $igrejaId = $igreja instanceof Igreja ? (int) $igreja->id : (int) $igreja;

        if ($igrejaId <= 0) {
            throw new \InvalidArgumentException('Igreja invalida para atualizar status operacional.');
        }

        $statusOperacional = $this->igrejaTemAdminLocalAtivo($igrejaId)
            ? 'operacional'
            : 'aguardando_admin_local';

        Igreja::query()
            ->whereKey($igrejaId)
            ->update([
                'status_operacional' => $statusOperacional,
            ]);

        return Igreja::query()->findOrFail($igrejaId);
    }

    public function atualizarPorUsuario(Usuario $usuario): void
    {
        $igrejasIds = $usuario->vinculosIgrejaAtivos()
            ->pluck('igreja_id')
            ->filter(fn (mixed $igrejaId): bool => is_numeric($igrejaId))
            ->map(fn (mixed $igrejaId): int => (int) $igrejaId)
            ->unique()
            ->values();

        foreach ($igrejasIds as $igrejaId) {
            $this->atualizar($igrejaId);
        }
    }

    private function igrejaTemAdminLocalAtivo(int $igrejaId): bool
    {
        return DB::table('usuario_igreja as ui')
            ->join('usuarios as u', 'u.id', '=', 'ui.usuario_id')
            ->join('usuario_igreja_papeis as uip', function ($join): void {
                $join->on('uip.usuario_igreja_id', '=', 'ui.id')
                    ->where('uip.papel', '=', PapelIgreja::ADMIN_LOCAL->value)
                    ->where('uip.ativo', '=', true)
                    ->whereNull('uip.revogado_em');
            })
            ->where('ui.igreja_id', $igrejaId)
            ->where('ui.ativo', true)
            ->where('u.ativo', true)
            ->exists();
    }
}
