<?php

namespace App\Services;

use App\Enums\PapelIgreja;
use App\Models\Usuario;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class Autorizador
{
    private bool $dadosNovosCarregados = false;

    private bool $novasTabelasDisponiveis = false;

    /**
     * @var array<int, array<int, string>>
     */
    private array $papeisPorIgreja = [];

    /**
     * @var int[]
     */
    private array $igrejasIds = [];

    public function __construct(
        private readonly ?Usuario $usuario
    ) {
    }

    public function temNivel(int $nivel): bool
    {
        if (!$this->usuario) {
            return false;
        }

        return $this->nivelGlobalAtual() >= $nivel;
    }

    public function ehSuperAdmin(): bool
    {
        return $this->ehAdminMaster();
    }

    public function ehAdminMaster(): bool
    {
        return (bool) ($this->usuario?->ehAdminMaster());
    }

    public function temPapelNaIgreja(string $papel, ?int $igrejaId = null): bool
    {
        $papel = trim($papel);

        if ($papel === '' || !$this->usuario) {
            return false;
        }

        $this->carregarDadosNovosSeNecessario();

        if ($igrejaId !== null) {
            $papeis = $this->papeisPorIgreja[$igrejaId] ?? [];

            return in_array($papel, $papeis, true);
        }

        foreach ($this->papeisPorIgreja as $papeis) {
            if (in_array($papel, $papeis, true)) {
                return true;
            }
        }

        return false;
    }

    public function papeisNaIgreja(?int $igrejaId = null): Collection
    {
        if (!$this->usuario) {
            return collect();
        }

        $this->carregarDadosNovosSeNecessario();

        if ($igrejaId !== null) {
            return collect($this->papeisPorIgreja[$igrejaId] ?? [])->values();
        }

        return collect($this->papeisPorIgreja)
            ->flatten()
            ->unique()
            ->values();
    }

    public function igrejasDoUsuario(): Collection
    {
        if (!$this->usuario) {
            return collect();
        }

        $this->carregarDadosNovosSeNecessario();

        return collect($this->igrejasIds)->values();
    }

    public function pode(string $acao, ?int $igrejaId = null): bool
    {
        $acao = trim($acao);

        if ($acao === '') {
            return false;
        }

        if (in_array($acao, ['acessar_missa_publica', 'visualizar_letra_publica'], true)) {
            return true;
        }

        if (!$this->usuario) {
            return false;
        }

        return match ($acao) {
            'criar_igreja', 'editar_igreja', 'inativar_igreja', 'promover_admin_master',
            'inativar_conta', 'reativar_conta', 'visualizar_logs', 'visualizar_auditoria',
            'gerir_manutencao', 'enviar_notificacao_global', 'vincular_usuario_igreja',
            'remover_vinculo_usuario', 'inativar_musica', 'inativar_versao_musical',
            'inativar_acorde' => $this->ehAdminMaster(),
            'cadastrar_musico', 'editar_musico', 'vincular_musico_existente', 'resetar_senha_musico' => $this->ehAdminMaster()
                || $this->temAlgumPapelNaIgreja([PapelIgreja::ADMIN_LOCAL->value, PapelIgreja::COORDENADOR->value], $igrejaId),
            'conceder_papel_local_admin_local' => $this->ehAdminMaster()
                || $this->temPapelNaIgreja(PapelIgreja::COORDENADOR->value, $igrejaId),
            'conceder_papel_local', 'revogar_papel_local' => $this->ehAdminMaster()
                || $this->temAlgumPapelNaIgreja([PapelIgreja::ADMIN_LOCAL->value, PapelIgreja::COORDENADOR->value], $igrejaId),
            'cadastrar_missa', 'editar_missa', 'publicar_missa', 'editar_repertorio' => $this->ehAdminMaster()
                || $this->temPapelNaIgreja(PapelIgreja::ADMIN_LOCAL->value, $igrejaId),
            'cadastrar_musica', 'editar_musica', 'cadastrar_acorde', 'editar_acorde',
            'cadastrar_versao_musical', 'editar_versao_musical' => $this->ehAdminMaster()
                || $this->temPapelNaIgreja(PapelIgreja::COORDENADOR->value, $igrejaId),
            'resetar_senha_admin_local', 'resetar_senha_coordenador' => $this->ehAdminMaster(),
            'acessar_suporte' => (bool) ($this->usuario->ativo ?? false),
            'acessar_painel_global' => $this->ehAdminMaster(),
            'acessar_painel_igreja' => $this->temAlgumPapelNaIgreja([
                PapelIgreja::ADMIN_LOCAL->value,
                PapelIgreja::COORDENADOR->value,
                PapelIgreja::MUSICO->value,
            ], $igrejaId),
            default => false,
        };
    }

    private function nivelGlobalAtual(): int
    {
        if (!$this->usuario) {
            return 0;
        }

        if ($this->usuario->ehAdminMaster()) {
            return 6;
        }

        $nivel = (int) ($this->usuario->nivel_global ?? 0);

        if ($nivel >= 1 && $nivel <= 6) {
            return $nivel;
        }

        return 1;
    }

    private function carregarDadosNovosSeNecessario(): void
    {
        if ($this->dadosNovosCarregados) {
            return;
        }

        $this->dadosNovosCarregados = true;

        if (!$this->usuario) {
            return;
        }

        if (!Schema::hasTable('usuario_igreja') || !Schema::hasTable('usuario_igreja_papeis')) {
            return;
        }

        $registros = DB::table('usuario_igreja as ui')
            ->leftJoin('usuario_igreja_papeis as uip', function ($join): void {
                $join->on('uip.usuario_igreja_id', '=', 'ui.id')
                    ->where('uip.ativo', '=', true)
                    ->whereNull('uip.revogado_em');
            })
            ->where('ui.usuario_id', $this->usuario->id)
            ->where('ui.ativo', true)
            ->select('ui.igreja_id', 'uip.papel')
            ->get();

        if ($registros->isEmpty()) {
            return;
        }

        foreach ($registros as $registro) {
            $igrejaId = (int) $registro->igreja_id;
            $this->igrejasIds[$igrejaId] = $igrejaId;

            $papel = is_string($registro->papel) ? trim($registro->papel) : '';

            if ($papel === '') {
                continue;
            }

            $this->papeisPorIgreja[$igrejaId] ??= [];

            if (!in_array($papel, $this->papeisPorIgreja[$igrejaId], true)) {
                $this->papeisPorIgreja[$igrejaId][] = $papel;
            }
        }
    }

    private function temAlgumPapelNaIgreja(array $papeis, ?int $igrejaId = null): bool
    {
        foreach ($papeis as $papel) {
            if ($this->temPapelNaIgreja($papel, $igrejaId)) {
                return true;
            }
        }

        return false;
    }
}
