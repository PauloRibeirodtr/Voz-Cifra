<?php

namespace App\Services;

use App\Enums\PapelIgreja;
use App\Models\Igreja;
use App\Models\NotificacaoInterna;
use App\Models\SolicitacaoMudancaTom;
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

    public function statusContaAlterado(Usuario $usuario, bool $ativo, ?Usuario $ator = null): void
    {
        $this->criar(
            usuario: $usuario,
            tipo: $ativo ? 'conta_reativada' : 'conta_inativada',
            titulo: $ativo ? 'Conta reativada' : 'Conta inativada',
            mensagem: $ativo
                ? 'Seu acesso ao Voz & Cifra foi reativado.'
                : 'Seu acesso ao Voz & Cifra foi inativado. Fale com a administracao se precisar de ajuda.',
            url: null,
            ator: $ator,
            igreja: $usuario->igrejaAtiva(),
            dados: [
                'ativo' => $ativo,
            ]
        );
    }

    public function senhaRedefinida(Usuario $usuario, ?Usuario $ator = null): void
    {
        $this->criar(
            usuario: $usuario,
            tipo: 'reset_senha',
            titulo: 'Link de acesso enviado',
            mensagem: 'Um novo link de definicao de senha foi gerado para sua conta.',
            url: null,
            ator: $ator,
            igreja: $usuario->igrejaAtiva()
        );
    }

    public function pedidoMudancaTomCriado(Usuario $destinatario, SolicitacaoMudancaTom $solicitacao, ?Usuario $ator = null): void
    {
        $solicitacao->loadMissing(['missa', 'missaMusica.musica', 'igreja']);

        $this->criar(
            usuario: $destinatario,
            tipo: 'pedido_mudanca_tom',
            titulo: 'Pedido de mudanca de tom',
            mensagem: sprintf(
                '%s pediu para tocar %s em %s na missa %s.',
                $ator?->nome ?: 'Um musico',
                $solicitacao->missaMusica?->musica?->titulo ?: 'uma musica',
                $solicitacao->tom_sugerido,
                $solicitacao->missa?->titulo ?: 'selecionada'
            ),
            url: route('local-admin.missas.show', $solicitacao->missa_id, false) . '#repertorio-item-' . $solicitacao->missa_musica_id,
            ator: $ator,
            igreja: $solicitacao->igreja,
            dados: [
                'solicitacao_id' => $solicitacao->id,
                'missa_id' => $solicitacao->missa_id,
                'missa_musica_id' => $solicitacao->missa_musica_id,
                'tom_atual' => $solicitacao->tom_atual,
                'tom_sugerido' => $solicitacao->tom_sugerido,
            ]
        );
    }

    public function pedidoMudancaTomRespondido(SolicitacaoMudancaTom $solicitacao, ?Usuario $ator = null): void
    {
        $solicitacao->loadMissing(['missa', 'missaMusica.musica', 'igreja', 'usuario']);

        $aprovada = $solicitacao->status === SolicitacaoMudancaTom::STATUS_APROVADA;

        $this->criar(
            usuario: $solicitacao->usuario,
            tipo: $aprovada ? 'pedido_mudanca_tom_aprovado' : 'pedido_mudanca_tom_recusado',
            titulo: $aprovada ? 'Mudanca de tom aprovada' : 'Mudanca de tom recusada',
            mensagem: sprintf(
                '%s: %s para %s.',
                $solicitacao->missaMusica?->musica?->titulo ?: 'Musica',
                $aprovada ? 'tom alterado' : 'pedido analisado',
                $solicitacao->tom_sugerido
            ),
            url: route('member.repertorio', [], false) . '#repertorio-item-' . $solicitacao->missa_musica_id,
            ator: $ator,
            igreja: $solicitacao->igreja,
            dados: [
                'solicitacao_id' => $solicitacao->id,
                'missa_id' => $solicitacao->missa_id,
                'missa_musica_id' => $solicitacao->missa_musica_id,
                'tom_atual' => $solicitacao->tom_atual,
                'tom_sugerido' => $solicitacao->tom_sugerido,
                'status' => $solicitacao->status,
            ]
        );
    }

    private function disponivel(): bool
    {
        return Schema::hasTable('notificacoes_internas');
    }
}
