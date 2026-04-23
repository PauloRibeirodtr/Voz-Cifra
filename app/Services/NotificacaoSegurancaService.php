<?php

namespace App\Services;

use App\Mail\NotificacaoSegurancaMail;
use App\Enums\PapelIgreja;
use App\Models\AuditoriaEvento;
use App\Models\HistoricoEnvioEmail;
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
    public function enviarEventoConta(
        Usuario $alvo,
        string $evento,
        ?Usuario $ator = null,
        array $contexto = []
    ): void {
        if (!$this->podeEnviar($evento)) {
            return;
        }

        $contexto = $this->normalizarContexto($alvo, $evento, $ator, $contexto);

        if (!filter_var((string) $alvo->email, FILTER_VALIDATE_EMAIL)) {
            $this->registrarHistoricoEmailCancelado($alvo, $evento, $contexto, 'Email invalido ou ausente.');

            return;
        }

        $auditoriaEvento = $this->registrarAuditoria($alvo, $evento, $ator, $contexto);
        $mailable = new NotificacaoSegurancaMail(
            evento: $evento,
            alvo: $alvo,
            ator: $ator,
            contexto: $contexto
        );
        $historicoEmail = $this->registrarHistoricoEmail($alvo, $evento, $contexto, $auditoriaEvento, $mailable);

        try {
            $mensagem = Mail::to($alvo->email);

            $ccAdmin = (string) config('notificacoes.cc_admin');
            if ($ccAdmin !== '' && filter_var($ccAdmin, FILTER_VALIDATE_EMAIL)) {
                $mensagem->cc($ccAdmin);
            }

            $mensagem->send($mailable);

            $this->marcarAuditoriaComoEnviada($auditoriaEvento);
            $this->marcarHistoricoComoEnviado($historicoEmail);

            Log::info('Notificacao de seguranca enviada.', [
                'evento' => $evento,
                'alvo_id' => $alvo->id,
                'protocolo' => $contexto['protocolo'] ?? null,
            ]);
        } catch (Throwable $e) {
            $this->marcarAuditoriaComoFalha($auditoriaEvento, $e);
            $this->marcarHistoricoComoFalha($historicoEmail, $e);

            Log::warning('Falha ao enviar notificacao de seguranca.', [
                'evento' => $evento,
                'alvo_id' => $alvo->id,
                'protocolo' => $contexto['protocolo'] ?? null,
                'erro' => $e->getMessage(),
            ]);
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
        $contexto['canal_suporte_url'] = $contexto['canal_suporte_url'] ?? trim((string) config('notificacoes.canal_suporte_url', ''));

        if ($ator) {
            $contexto['responsavel_nome'] = $contexto['responsavel_nome'] ?? $ator->nome;
            $contexto['responsavel_funcao'] = $contexto['responsavel_funcao'] ?? $this->descreverFuncao($ator);
        }

        $igrejaNome = $this->resolverNomeIgreja($alvo, $contexto);
        if ($igrejaNome !== null) {
            $contexto['igreja_nome'] = $contexto['igreja_nome'] ?? $igrejaNome;
        }

        unset($contexto['ator_email']);

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

    public function descreverFuncaoPublica(Usuario $usuario): string
    {
        return $this->descreverFuncao($usuario);
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

        return $alvo->igrejaAtiva()?->nome ?? $alvo->igreja?->nome;
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
                'igreja_id' => $contexto['igreja_id'] ?? $alvo->igrejaAtiva()?->id ?? $alvo->igreja_id,
                'igreja_nome' => $contexto['igreja_nome'] ?? $alvo->igrejaAtiva()?->nome ?? $alvo->igreja?->nome,
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

    private function registrarHistoricoEmail(
        Usuario $alvo,
        string $evento,
        array $contexto,
        ?AuditoriaEvento $auditoriaEvento,
        NotificacaoSegurancaMail $mailable
    ): ?HistoricoEnvioEmail {
        if (!Schema::hasTable('historico_envios_email')) {
            return null;
        }

        try {
            return HistoricoEnvioEmail::create([
                'usuario_id' => $alvo->id,
                'auditoria_evento_id' => $auditoriaEvento?->id,
                'origem_tipo' => $contexto['origem_tipo'] ?? $contexto['origem'] ?? null,
                'origem_id' => $contexto['origem_id'] ?? null,
                'destinatario_email' => $alvo->email,
                'destinatario_nome' => $alvo->nome,
                'tipo_email' => $evento,
                'assunto' => (string) ($mailable->envelope()->subject ?? 'Notificacao do sistema'),
                'status_envio' => 'pendente',
                'mailer' => (string) config('mail.default'),
                'payload' => [
                    'protocolo' => $contexto['protocolo'] ?? null,
                    'igreja_nome' => $contexto['igreja_nome'] ?? null,
                    'papel' => $contexto['papel'] ?? null,
                    'origem' => $contexto['origem'] ?? null,
                ],
            ]);
        } catch (Throwable $e) {
            Log::warning('Falha ao registrar historico de envio de email.', [
                'evento' => $evento,
                'alvo_id' => $alvo->id,
                'erro' => $e->getMessage(),
            ]);

            return null;
        }
    }

    private function registrarHistoricoEmailCancelado(
        Usuario $alvo,
        string $evento,
        array $contexto,
        string $motivo
    ): void {
        if (!Schema::hasTable('historico_envios_email')) {
            return;
        }

        try {
            HistoricoEnvioEmail::create([
                'usuario_id' => $alvo->id,
                'origem_tipo' => $contexto['origem_tipo'] ?? $contexto['origem'] ?? null,
                'origem_id' => $contexto['origem_id'] ?? null,
                'destinatario_email' => (string) $alvo->email,
                'destinatario_nome' => $alvo->nome,
                'tipo_email' => $evento,
                'assunto' => 'Notificacao nao enviada',
                'status_envio' => 'cancelado',
                'mensagem_retorno' => $motivo,
                'mailer' => (string) config('mail.default'),
                'payload' => [
                    'protocolo' => $contexto['protocolo'] ?? null,
                    'origem' => $contexto['origem'] ?? null,
                ],
            ]);
        } catch (Throwable $e) {
            Log::warning('Falha ao registrar historico cancelado de email.', [
                'evento' => $evento,
                'alvo_id' => $alvo->id,
                'erro' => $e->getMessage(),
            ]);
        }
    }

    private function marcarHistoricoComoEnviado(?HistoricoEnvioEmail $historico): void
    {
        if (!$historico) {
            return;
        }

        $historico->forceFill([
            'status_envio' => 'enviado',
            'mensagem_retorno' => null,
            'enviado_em' => now(),
        ])->save();
    }

    private function marcarHistoricoComoFalha(?HistoricoEnvioEmail $historico, Throwable $e): void
    {
        if (!$historico) {
            return;
        }

        $historico->forceFill([
            'status_envio' => 'falhou',
            'mensagem_retorno' => $e->getMessage(),
        ])->save();
    }
}
