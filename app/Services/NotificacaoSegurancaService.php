<?php

namespace App\Services;

use App\Mail\NotificacaoSegurancaMail;
use App\Models\AuditoriaEvento;
use App\Models\Igreja;
use App\Models\Usuario;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Throwable;

class NotificacaoSegurancaService
{
    public function __construct(
        private readonly TelegramNotificacaoService $telegramNotificacaoService,
    ) {
    }

    public function enviarEventoConta(
        Usuario $alvo,
        string $evento,
        ?Usuario $ator = null,
        array $contexto = []
    ): void {
        if (!$this->podeEnviar($evento)) {
            return;
        }

        if (!filter_var((string) $alvo->email, FILTER_VALIDATE_EMAIL)) {
            return;
        }

        $contexto = $this->normalizarContexto($alvo, $evento, $ator, $contexto);
        $auditoriaEvento = $this->registrarAuditoria($alvo, $evento, $ator, $contexto);

        try {
            $mensagem = Mail::to($alvo->email);

            $ccAdmin = (string) config('notificacoes.cc_admin');
            if ($ccAdmin !== '' && filter_var($ccAdmin, FILTER_VALIDATE_EMAIL)) {
                $mensagem->cc($ccAdmin);
            }

            $mensagem->send(new NotificacaoSegurancaMail(
                evento: $evento,
                alvo: $alvo,
                ator: $ator,
                contexto: $contexto
            ));

            $this->marcarAuditoriaComoEnviada($auditoriaEvento);

            Log::info('Notificacao de seguranca enviada.', [
                'evento' => $evento,
                'alvo_id' => $alvo->id,
                'protocolo' => $contexto['protocolo'] ?? null,
            ]);

            $this->telegramNotificacaoService->notificarEventoConta($alvo, $evento, $contexto);
        } catch (Throwable $e) {
            $this->marcarAuditoriaComoFalha($auditoriaEvento, $e);

            Log::warning('Falha ao enviar notificacao de seguranca.', [
                'evento' => $evento,
                'alvo_id' => $alvo->id,
                'protocolo' => $contexto['protocolo'] ?? null,
                'erro' => $e->getMessage(),
            ]);

            $this->telegramNotificacaoService->notificarEventoConta($alvo, $evento, $contexto);
        }
    }

    private function podeEnviar(string $evento): bool
    {
        if (!config('notificacoes.seguranca_ativa', false)) {
            return false;
        }

        $eventosHabilitados = config('notificacoes.eventos_habilitados', []);

        return in_array($evento, is_array($eventosHabilitados) ? $eventosHabilitados : [], true);
    }

    private function normalizarContexto(
        Usuario $alvo,
        string $evento,
        ?Usuario $ator,
        array $contexto
    ): array {
        $contexto['protocolo'] = $contexto['protocolo'] ?? $this->gerarProtocolo($evento);
        $contexto['canal_suporte'] = $contexto['canal_suporte'] ?? (string) config('notificacoes.canal_suporte');
        $contexto['canal_suporte_url'] = $contexto['canal_suporte_url'] ?? $this->gerarCanalSuporteUrl($contexto['protocolo']);

        if ($ator) {
            $contexto['responsavel_nome'] = $contexto['responsavel_nome'] ?? $ator->nome;
            $contexto['responsavel_funcao'] = $contexto['responsavel_funcao'] ?? $this->descreverFuncao($ator);
        }

        $igrejaNome = $this->resolverNomeIgreja($alvo, $contexto);
        if ($igrejaNome !== null) {
            $contexto['igreja_nome'] = $contexto['igreja_nome'] ?? $igrejaNome;
        }

        unset($contexto['igreja_id'], $contexto['ator_email']);

        return $contexto;
    }

    private function gerarProtocolo(string $evento): string
    {
        $prefixo = Str::upper((string) config('notificacoes.protocolo_prefixo', 'SEG'));
        $siglaEvento = Str::upper(Str::substr(preg_replace('/[^a-z]/i', '', $evento) ?: 'EV', 0, 3));

        return sprintf(
            '%s-%s-%s-%s',
            $prefixo,
            now('America/Cuiaba')->format('YmdHis'),
            str_pad($siglaEvento, 3, 'X'),
            Str::upper(Str::random(4))
        );
    }

    private function gerarCanalSuporteUrl(?string $protocolo): ?string
    {
        $baseUrl = trim((string) config('notificacoes.telegram_bot_base_url', ''));
        $username = trim((string) config('notificacoes.telegram_bot_username', ''));
        $protocolo = is_string($protocolo) ? trim($protocolo) : '';

        if ($baseUrl !== '') {
            return $this->anexarProtocoloNaUrl($baseUrl, $protocolo);
        }

        if ($username === '') {
            return null;
        }

        return $this->anexarProtocoloNaUrl('https://t.me/' . ltrim($username, '@'), $protocolo);
    }

    private function anexarProtocoloNaUrl(string $baseUrl, string $protocolo): string
    {
        $baseUrl = trim($baseUrl);

        if ($protocolo === '') {
            return $baseUrl;
        }

        $separador = str_contains($baseUrl, '?') ? '&' : '?';

        return $baseUrl . $separador . 'start=' . urlencode($protocolo);
    }

    private function descreverFuncao(Usuario $usuario): string
    {
        return match ($usuario->perfil_global) {
            'admin_master' => 'Admin master nivel ' . $usuario->nivelGlobal(),
            'admin_local' => 'Administrador local',
            'member' => 'Musico',
            default => 'Usuario do sistema',
        };
    }

    private function resolverNomeIgreja(Usuario $alvo, array $contexto): ?string
    {
        $igrejaNome = Arr::get($contexto, 'igreja_nome');
        if (is_string($igrejaNome) && trim($igrejaNome) !== '') {
            return trim($igrejaNome);
        }

        $igrejaId = Arr::get($contexto, 'igreja_id');
        if ($igrejaId) {
            return Igreja::query()->whereKey($igrejaId)->value('nome');
        }

        return $alvo->igreja?->nome;
    }

    private function registrarAuditoria(
        Usuario $alvo,
        string $evento,
        ?Usuario $ator,
        array $contexto
    ): ?AuditoriaEvento {
        if (!Schema::hasTable('auditoria_eventos')) {
            return null;
        }

        try {
            return AuditoriaEvento::create([
                'protocolo' => $contexto['protocolo'] ?? null,
                'evento' => $evento,
                'categoria' => 'seguranca',
                'ator_id' => $ator?->id,
                'ator_nome' => $ator?->nome,
                'ator_funcao' => $ator ? $this->descreverFuncao($ator) : null,
                'alvo_id' => $alvo->id,
                'alvo_nome' => $alvo->nome,
                'alvo_email' => $alvo->email,
                'igreja_id' => $contexto['igreja_id'] ?? $alvo->igreja_id,
                'igreja_nome' => $contexto['igreja_nome'] ?? $alvo->igreja?->nome,
                'contexto' => $contexto,
                'resultado' => 'registrado',
                'ip' => request()?->ip(),
                'user_agent' => request()?->userAgent(),
            ]);
        } catch (Throwable $e) {
            Log::warning('Falha ao registrar auditoria de seguranca.', [
                'evento' => $evento,
                'alvo_id' => $alvo->id,
                'protocolo' => $contexto['protocolo'] ?? null,
                'erro' => $e->getMessage(),
            ]);

            return null;
        }
    }

    private function marcarAuditoriaComoEnviada(?AuditoriaEvento $auditoriaEvento): void
    {
        if (!$auditoriaEvento) {
            return;
        }

        $auditoriaEvento->forceFill([
            'resultado' => 'email_enviado',
            'notificacao_enviada_em' => now(),
            'erro_envio' => null,
        ])->save();
    }

    private function marcarAuditoriaComoFalha(?AuditoriaEvento $auditoriaEvento, Throwable $e): void
    {
        if (!$auditoriaEvento) {
            return;
        }

        $auditoriaEvento->forceFill([
            'resultado' => 'email_falhou',
            'erro_envio' => $e->getMessage(),
        ])->save();
    }
}
