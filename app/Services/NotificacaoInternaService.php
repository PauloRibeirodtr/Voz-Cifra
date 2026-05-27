<?php

namespace App\Services;

use App\Enums\PapelIgreja;
use App\Models\Igreja;
use App\Models\NotificacaoInterna;
use App\Models\Usuario;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class NotificacaoInternaService
{
    public function criar(
        Usuario $usuario,
        string $tipo,
        string $titulo,
        ?string $mensagem = null,
        ?string $url = null,
        ?Usuario $ator = null,
        ?Igreja $igreja = null,
        array $dados = []
    ): ?NotificacaoInterna {
        if (!$this->disponivel()) {
            return null;
        }

        try {
            return NotificacaoInterna::create([
                'usuario_id' => $usuario->id,
                'ator_id' => $ator?->id,
                'igreja_id' => $igreja?->id,
                'tipo' => $tipo,
                'titulo' => $titulo,
                'mensagem' => $mensagem,
                'url' => $url,
                'dados' => $dados,
            ]);
        } catch (\Throwable $e) {
            Log::warning('Falha ao criar notificacao interna.', [
                'usuario_id' => $usuario->id,
                'tipo' => $tipo,
                'erro' => $e->getMessage(),
            ]);

            return null;
        }
    }

    public function papelConcedido(Usuario $usuario, Igreja $igreja, PapelIgreja $papel, ?Usuario $ator = null): void
    {
        $this->criar(
            usuario: $usuario,
            tipo: 'papel_concedido',
            titulo: 'Novo acesso liberado',
            mensagem: sprintf('Voce agora atua como %s em %s.', mb_strtolower($papel->label()), $igreja->nome),
            url: null,
            ator: $ator,
            igreja: $igreja,
            dados: [
                'papel' => $papel->value,
                'papel_label' => $papel->label(),
                'igreja_nome' => $igreja->nome,
            ]
        );
    }

    public function papelRevogado(Usuario $usuario, Igreja $igreja, PapelIgreja $papel, ?Usuario $ator = null): void
    {
        $this->criar(
            usuario: $usuario,
            tipo: 'papel_revogado',
            titulo: 'Acesso atualizado',
            mensagem: sprintf('Seu papel de %s em %s foi removido.', mb_strtolower($papel->label()), $igreja->nome),
            url: null,
            ator: $ator,
            igreja: $igreja,
            dados: [
                'papel' => $papel->value,
                'papel_label' => $papel->label(),
                'igreja_nome' => $igreja->nome,
            ]
        );
    }

    private function disponivel(): bool
    {
        return Schema::hasTable('notificacoes_internas');
    }
}
