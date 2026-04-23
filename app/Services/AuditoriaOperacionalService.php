<?php

namespace App\Services;

use App\Enums\PapelIgreja;
use App\Models\AuditoriaEvento;
use App\Models\Igreja;
use App\Models\Usuario;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class AuditoriaOperacionalService
{
    public function registrar(
        string $evento,
        ?Usuario $ator = null,
        ?Usuario $alvo = null,
        Igreja|int|null $igreja = null,
        array $contexto = [],
        string $resultado = 'registrado'
    ): ?AuditoriaEvento {
        if (!Schema::hasTable('auditoria_eventos')) {
            return null;
        }

        $igrejaModel = $this->resolverIgreja($igreja);
        $contexto = $this->normalizarContexto($evento, $contexto, $igrejaModel);

        return AuditoriaEvento::query()->create([
            'protocolo' => $contexto['protocolo'],
            'evento' => $evento,
            'categoria' => 'operacao',
            'ator_id' => $ator?->id,
            'ator_nome' => $ator?->nome,
            'ator_funcao' => $ator ? $this->descreverFuncao($ator) : null,
            'alvo_id' => $alvo?->id,
            'alvo_nome' => $alvo?->nome,
            'alvo_email' => $alvo?->email,
            'igreja_id' => $igrejaModel?->id,
            'igreja_nome' => $igrejaModel?->nome,
            'contexto' => $contexto,
            'resultado' => $resultado,
            'ip' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
        ]);
    }

    private function normalizarContexto(string $evento, array $contexto, ?Igreja $igreja): array
    {
        $contexto['protocolo'] = $contexto['protocolo'] ?? $this->gerarProtocolo($evento);

        if ($igreja) {
            $contexto['igreja_id'] = $contexto['igreja_id'] ?? $igreja->id;
            $contexto['igreja_nome'] = $contexto['igreja_nome'] ?? $igreja->nome;
        }

        return $contexto;
    }

    private function resolverIgreja(Igreja|int|null $igreja): ?Igreja
    {
        if ($igreja instanceof Igreja) {
            return $igreja;
        }

        if (is_int($igreja) && $igreja > 0) {
            return Igreja::query()->find($igreja);
        }

        return null;
    }

    private function gerarProtocolo(string $evento): string
    {
        $siglaEvento = Str::upper(Str::substr(preg_replace('/[^a-z]/i', '', $evento) ?: 'EV', 0, 4));

        return sprintf(
            'AUD-%s-%s-%s',
            now('America/Sao_Paulo')->format('YmdHis'),
            str_pad($siglaEvento, 4, 'X'),
            Str::upper(Str::random(4))
        );
    }

    private function descreverFuncao(Usuario $usuario): string
    {
        if ($usuario->ehAdminMaster()) {
            return 'Admin master';
        }

        $papeis = $usuario->listarPapeisNaIgreja($usuario->igrejaAtiva()?->id)
            ->map(fn (PapelIgreja $papel): string => $papel->label())
            ->values();

        if ($usuario->ehPadre()) {
            $papeis->prepend('Padre');
        }

        return $papeis->isNotEmpty()
            ? $papeis->unique()->implode(' / ')
            : 'Usuario do sistema';
    }
}
