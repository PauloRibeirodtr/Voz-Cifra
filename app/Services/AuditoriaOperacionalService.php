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
        $contexto['risco'] = $contexto['risco'] ?? $this->classificarRisco($evento);

        if ($igreja) {
            $contexto['igreja_id'] = $contexto['igreja_id'] ?? $igreja->id;
            $contexto['igreja_nome'] = $contexto['igreja_nome'] ?? $igreja->nome;
        }

        return $this->sanitizarContexto($contexto);
    }

    private function sanitizarContexto(array $contexto): array
    {
        foreach ($contexto as $chave => $valor) {
            $chaveNormalizada = Str::lower((string) $chave);

            if (str_contains($chaveNormalizada, 'senha') || str_contains($chaveNormalizada, 'password')) {
                $contexto[$chave] = '[protegido]';
                continue;
            }

            if (str_contains($chaveNormalizada, 'token') || str_contains($chaveNormalizada, 'definir_senha_url')) {
                $contexto[$chave] = '[protegido]';
                continue;
            }

            if (str_contains($chaveNormalizada, 'cpf')) {
                $contexto[$chave] = is_scalar($valor) ? $this->mascararCpf((string) $valor) : '[protegido]';
                continue;
            }

            if (str_contains($chaveNormalizada, 'telefone')) {
                $contexto[$chave] = is_scalar($valor) ? $this->mascararTelefone((string) $valor) : '[protegido]';
                continue;
            }

            if (str_contains($chaveNormalizada, 'email')) {
                $contexto[$chave] = is_scalar($valor) ? $this->mascararEmail((string) $valor) : '[protegido]';
                continue;
            }

            if (is_array($valor)) {
                $contexto[$chave] = $this->sanitizarContexto($valor);
            }
        }

        return $contexto;
    }

    private function classificarRisco(string $evento): string
    {
        return match ($evento) {
            'reset_senha',
            'conta_inativada',
            'conta_reativada',
            'troca_nivel_global',
            'papel_local_concedido',
            'papel_local_revogado',
            'admin_local_vinculado',
            'admin_local_revogado',
            'coordenador_vinculado',
            'coordenador_revogado' => 'alto',

            'usuario_criado',
            'usuario_editado',
            'musico_vinculado',
            'musico_revogado',
            'igreja_criada',
            'igreja_editada',
            'missa_criada',
            'missa_editada',
            'musica_inativada',
            'versao_musical_inativada' => 'medio',

            default => 'baixo',
        };
    }

    private function mascararCpf(string $cpf): string
    {
        $numeros = preg_replace('/\D+/', '', $cpf) ?? '';

        if (strlen($numeros) !== 11) {
            return '[protegido]';
        }

        return substr($numeros, 0, 3) . '.***.***-' . substr($numeros, -2);
    }

    private function mascararTelefone(string $telefone): string
    {
        $numeros = preg_replace('/\D+/', '', $telefone) ?? '';

        if (strlen($numeros) < 8) {
            return '[protegido]';
        }

        return str_repeat('*', max(strlen($numeros) - 4, 0)) . substr($numeros, -4);
    }

    private function mascararEmail(string $email): string
    {
        $email = trim($email);

        if (!str_contains($email, '@')) {
            return '[protegido]';
        }

        [$local, $dominio] = explode('@', $email, 2);
        $prefixo = Str::substr($local, 0, 2);

        return $prefixo . '***@' . $dominio;
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
