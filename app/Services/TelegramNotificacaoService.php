<?php

namespace App\Services;

use App\Models\Chamado;
use App\Models\TelegramVinculo;
use App\Models\Usuario;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Throwable;

class TelegramNotificacaoService
{
    public function notificarEventoConta(Usuario $usuario, string $evento, array $contexto = []): void
    {
        $vinculo = $this->obterVinculoAtivo($usuario);

        if (!$vinculo) {
            return;
        }

        $mensagem = $this->montarMensagemEventoConta($usuario, $evento, $contexto);

        if ($mensagem === null) {
            return;
        }

        $this->enviarMensagem($vinculo->chat_id, $mensagem);
    }

    public function notificarAtualizacaoChamado(Chamado $chamado, string $tipoAtualizacao, ?string $mensagem = null): void
    {
        if (!$chamado->solicitante) {
            return;
        }

        $vinculo = $this->obterVinculoAtivo($chamado->solicitante);

        if (!$vinculo) {
            return;
        }

        $texto = $this->montarMensagemChamado($chamado, $tipoAtualizacao, $mensagem);
        $this->enviarMensagem($vinculo->chat_id, $texto);
    }

    private function obterVinculoAtivo(Usuario $usuario): ?TelegramVinculo
    {
        if (!Schema::hasTable('telegram_vinculos')) {
            return null;
        }

        return $usuario->telegramVinculos()
            ->where('ativo', true)
            ->latest('ultimo_acesso_em')
            ->latest('id')
            ->first();
    }

    private function montarMensagemEventoConta(Usuario $usuario, string $evento, array $contexto): ?string
    {
        $titulo = match ($evento) {
            'conta_inativada' => 'Sua conta foi inativada.',
            'conta_reativada' => 'Sua conta foi reativada.',
            'reset_senha' => 'Sua senha foi redefinida.',
            default => null,
        };

        if ($titulo === null) {
            return null;
        }

        $linhas = [
            'Voz & Cifra',
            '',
            $titulo,
            'Usuario: ' . $usuario->nome,
        ];

        if (!empty($contexto['igreja_nome'])) {
            $linhas[] = 'Igreja: ' . $contexto['igreja_nome'];
        }

        if (!empty($contexto['protocolo'])) {
            $linhas[] = 'Protocolo: ' . $contexto['protocolo'];
        }

        $linhas[] = '';
        $linhas[] = 'Se precisar, continue pelo suporte do bot usando o protocolo acima.';

        return implode("\n", $linhas);
    }

    private function montarMensagemChamado(Chamado $chamado, string $tipoAtualizacao, ?string $mensagem = null): string
    {
        $linhas = [
            'Atualizacao do seu atendimento',
            '',
            'Protocolo: ' . $chamado->protocolo,
            'Status: ' . app(ChamadoSupportService::class)->statusLabel($chamado->status),
            'Titulo: ' . $chamado->titulo,
        ];

        if ($tipoAtualizacao === 'mensagem' && $mensagem) {
            $linhas[] = '';
            $linhas[] = 'Nova resposta do suporte:';
            $linhas[] = $mensagem;
        }

        if ($tipoAtualizacao === 'status') {
            $linhas[] = '';
            $linhas[] = 'O status do seu chamado foi atualizado.';
        }

        return implode("\n", $linhas);
    }

    private function enviarMensagem(string $chatId, string $mensagem): void
    {
        $token = trim((string) config('notificacoes.telegram_bot_token', ''));

        if ($token === '') {
            return;
        }

        try {
            Http::baseUrl('https://api.telegram.org')
                ->asForm()
                ->timeout(10)
                ->post("/bot{$token}/sendMessage", [
                    'chat_id' => $chatId,
                    'text' => $mensagem,
                ])
                ->throw();
        } catch (Throwable $e) {
            Log::warning('Falha ao enviar notificacao pelo Telegram.', [
                'chat_id' => $chatId,
                'erro' => $e->getMessage(),
            ]);
        }
    }
}
