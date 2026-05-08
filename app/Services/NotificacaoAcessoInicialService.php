<?php

namespace App\Services;

use App\Mail\ConviteAcessoInicialMail;
use App\Models\AuditoriaEvento;
use App\Models\HistoricoEnvioEmail;
use App\Models\Usuario;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Throwable;

class NotificacaoAcessoInicialService
{
    public function __construct(
        private readonly NotificacaoSegurancaService $notificacaoSegurancaService
    ) {
    }

    public function enviarConvite(
        Usuario $alvo,
        ?Usuario $ator = null,
        array $contexto = []
    ): void {
        if (!$this->podeEnviar()) {
            return;
        }

        if (!filter_var((string) $alvo->email, FILTER_VALIDATE_EMAIL)) {
            $this->registrarHistoricoCancelado($alvo, $contexto, 'Email invalido ou ausente para convite inicial.');

            return;
        }

        $contexto = $this->normalizarContexto($alvo, $ator, $contexto);
        $contexto['definir_senha_url'] = $this->gerarLinkDefinicaoSenha($alvo);
        $contexto['expira_em_minutos'] = (int) config('auth.passwords.users.expire', 60);
        $mailable = new ConviteAcessoInicialMail(
            alvo: $alvo,
            ator: $ator,
            contexto: $contexto
        );
        $auditoriaEvento = $this->registrarAuditoria($alvo, $ator, $contexto);
        $historicoEmail = $this->registrarHistorico($alvo, $contexto, $auditoriaEvento, $mailable);

        try {
            $mensagem = Mail::to($alvo->email);

            $cc = (string) config('notificacoes.cc_acesso_inicial');
            if ($cc !== '' && filter_var($cc, FILTER_VALIDATE_EMAIL)) {
                $mensagem->cc($cc);
            }

            $mensagem->send($mailable);

            $this->marcarAuditoriaComoEnviada($auditoriaEvento);
            $this->marcarHistoricoComoEnviado($historicoEmail);
        } catch (Throwable $e) {
            $this->marcarAuditoriaComoFalha($auditoriaEvento, $e);
            $this->marcarHistoricoComoFalha($historicoEmail, $e);

            Log::warning('Falha ao enviar convite de acesso inicial.', [
                'alvo_id' => $alvo->id,
                'erro' => $e->getMessage(),
            ]);
        }
    }

    private function podeEnviar(): bool
    {
        return (bool) config('notificacoes.acesso_inicial_ativa', true);
    }

    private function normalizarContexto(Usuario $alvo, ?Usuario $ator, array $contexto): array
    {
        $contexto['protocolo'] = $contexto['protocolo'] ?? $this->gerarProtocolo();

        if ($ator) {
            $contexto['responsavel_nome'] = $contexto['responsavel_nome'] ?? $ator->nome;
            $contexto['responsavel_funcao'] = $contexto['responsavel_funcao'] ?? $this->notificacaoSegurancaService->descreverFuncaoPublica($ator);
        }

        if (!isset($contexto['igreja_nome'])) {
            $contexto['igreja_nome'] = $alvo->igrejaAtiva()?->nome ?? $alvo->igreja?->nome;
        }

        return $contexto;
    }

    private function gerarProtocolo(): string
    {
        return sprintf(
            'ACC-%s-%s',
            now('America/Sao_Paulo')->format('YmdHis'),
            Str::upper(Str::random(4))
        );
    }

    private function registrarAuditoria(
        Usuario $alvo,
        ?Usuario $ator,
        array $contexto
    ): ?AuditoriaEvento {
        if (!Schema::hasTable('auditoria_eventos')) {
            return null;
        }

        try {
            return AuditoriaEvento::create([
                'protocolo' => $contexto['protocolo'] ?? null,
                'evento' => 'convite_acesso_inicial',
                'categoria' => 'acesso',
                'ator_id' => $ator?->id,
                'ator_nome' => $ator?->nome,
                'ator_funcao' => $ator ? $this->notificacaoSegurancaService->descreverFuncaoPublica($ator) : null,
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
            Log::warning('Falha ao registrar auditoria do convite inicial.', [
                'alvo_id' => $alvo->id,
                'erro' => $e->getMessage(),
            ]);

            return null;
        }
    }

    private function registrarHistorico(
        Usuario $alvo,
        array $contexto,
        ?AuditoriaEvento $auditoriaEvento,
        ConviteAcessoInicialMail $mailable
    ): ?HistoricoEnvioEmail {
        if (!Schema::hasTable('historico_envios_email')) {
            return null;
        }

        try {
            return HistoricoEnvioEmail::create([
                'usuario_id' => $alvo->id,
                'auditoria_evento_id' => $auditoriaEvento?->id,
                'origem_tipo' => $contexto['origem_tipo'] ?? $contexto['origem'] ?? 'convite_acesso_inicial',
                'origem_id' => $contexto['origem_id'] ?? null,
                'destinatario_email' => $alvo->email,
                'destinatario_nome' => $alvo->nome,
                'tipo_email' => 'convite_acesso_inicial',
                'assunto' => (string) ($mailable->envelope()->subject ?? 'Convite de acesso'),
                'status_envio' => 'pendente',
                'mailer' => (string) config('mail.default'),
                'payload' => [
                    'protocolo' => $contexto['protocolo'] ?? null,
                    'origem' => $contexto['origem'] ?? null,
                    'igreja_nome' => $contexto['igreja_nome'] ?? null,
                    'definir_senha_url' => $contexto['definir_senha_url'] ?? null,
                    'expira_em_minutos' => $contexto['expira_em_minutos'] ?? null,
                ],
            ]);
        } catch (Throwable $e) {
            Log::warning('Falha ao registrar historico do convite inicial.', [
                'alvo_id' => $alvo->id,
                'erro' => $e->getMessage(),
            ]);

            return null;
        }
    }

    private function registrarHistoricoCancelado(Usuario $alvo, array $contexto, string $motivo): void
    {
        if (!Schema::hasTable('historico_envios_email')) {
            return;
        }

        try {
            HistoricoEnvioEmail::create([
                'usuario_id' => $alvo->id,
                'origem_tipo' => $contexto['origem_tipo'] ?? $contexto['origem'] ?? 'convite_acesso_inicial',
                'origem_id' => $contexto['origem_id'] ?? null,
                'destinatario_email' => (string) $alvo->email,
                'destinatario_nome' => $alvo->nome,
                'tipo_email' => 'convite_acesso_inicial',
                'assunto' => 'Convite de acesso nao enviado',
                'status_envio' => 'cancelado',
                'mensagem_retorno' => $motivo,
                'mailer' => (string) config('mail.default'),
                'payload' => [
                    'origem' => $contexto['origem'] ?? null,
                ],
            ]);
        } catch (Throwable $e) {
            Log::warning('Falha ao registrar historico cancelado do convite inicial.', [
                'alvo_id' => $alvo->id,
                'erro' => $e->getMessage(),
            ]);
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

    private function gerarLinkDefinicaoSenha(Usuario $alvo): string
    {
        $email = Str::lower(trim((string) $alvo->email));
        $token = Str::random(64);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $email],
            [
                'token' => hash('sha256', $token),
                'created_at' => now(),
            ]
        );

        return route('password.setup', [
            'email' => $email,
            'token' => $token,
        ]);
    }
}
