<?php

namespace App\Services;

use App\Enums\PapelIgreja;
use App\Mail\NotificacaoSistemaMail;
use App\Models\AuditoriaEvento;
use App\Models\HistoricoEnvioEmail;
use App\Models\Igreja;
use App\Models\Usuario;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Throwable;

class NotificacaoSistemaService
{
    public function __construct(
        private readonly NotificacaoSegurancaService $notificacaoSegurancaService
    ) {
    }

    public function enviarParaTodosUsuariosAtivos(
        string $evento,
        ?Usuario $ator = null,
        array $contexto = []
    ): void {
        if (!$this->podeEnviar($evento)) {
            return;
        }

        $usuarios = Usuario::query()
            ->where('ativo', true)
            ->orderBy('id')
            ->get();

        $this->enviarParaUsuarios($usuarios, $evento, $ator, $contexto);
    }

    public function enviarParaUsuarios(
        Collection $usuarios,
        string $evento,
        ?Usuario $ator = null,
        array $contexto = []
    ): void {
        if (!$this->podeEnviar($evento)) {
            return;
        }

        $contextoNormalizado = $this->normalizarContexto($evento, $ator, $contexto);

        $usuarios
            ->unique('id')
            ->each(function (Usuario $usuario) use ($evento, $ator, $contextoNormalizado): void {
                $this->enviarParaUsuario($usuario, $evento, $ator, $contextoNormalizado);
            });
    }

    public function enviarParaUsuariosOperacionaisAtivos(
        string $evento,
        ?Usuario $ator = null,
        array $contexto = [],
        Igreja|int|null $igreja = null,
        array $papeis = [
            PapelIgreja::ADMIN_LOCAL,
            PapelIgreja::COORDENADOR,
            PapelIgreja::MUSICO,
        ],
        bool $incluirAdminMaster = true
    ): void {
        if (!$this->podeEnviar($evento)) {
            return;
        }

        $igrejaId = $igreja instanceof Igreja
            ? (int) $igreja->id
            : (is_int($igreja) && $igreja > 0 ? $igreja : null);

        if ($igrejaId !== null) {
            $contexto['igreja_id'] = $contexto['igreja_id'] ?? $igrejaId;
            $contexto['igreja_nome'] = $contexto['igreja_nome']
                ?? Igreja::query()->whereKey($igrejaId)->value('nome');
        }

        $papeisNormalizados = collect($papeis)
            ->map(fn (PapelIgreja|string $papel): string => PapelIgreja::fromValue($papel)->value)
            ->unique()
            ->values()
            ->all();

        $usuarios = Usuario::query()
            ->where('ativo', true)
            ->where(function ($query) use ($incluirAdminMaster, $igrejaId, $papeisNormalizados): void {
                if ($incluirAdminMaster) {
                    $query->orWhere('perfil_global', 'admin_master');
                }

                $query->orWhereHas('vinculosIgreja', function ($vinculos) use ($igrejaId, $papeisNormalizados): void {
                    $vinculos->where('ativo', true)
                        ->when($igrejaId !== null, fn ($consulta) => $consulta->where('igreja_id', $igrejaId))
                        ->whereHas('papeisAtivos', fn ($papeisAtivos) => $papeisAtivos->whereIn('papel', $papeisNormalizados));
                });
            })
            ->orderBy('id')
            ->get();

        $this->enviarParaUsuarios($usuarios, $evento, $ator, $contexto);
    }

    private function enviarParaUsuario(
        Usuario $usuario,
        string $evento,
        ?Usuario $ator,
        array $contexto
    ): void {
        if (!$usuario->recebeNotificacoesEmail()) {
            $this->registrarHistoricoCancelado($usuario, $evento, $contexto, 'Usuario optou por nao receber avisos por e-mail.');

            return;
        }

        if (!filter_var((string) $usuario->email, FILTER_VALIDATE_EMAIL)) {
            $this->registrarHistoricoCancelado($usuario, $evento, $contexto, 'Email invalido ou ausente.');

            return;
        }

        $auditoriaEvento = $this->registrarAuditoria($usuario, $evento, $ator, $contexto);
        $mailable = new NotificacaoSistemaMail(
            evento: $evento,
            contexto: $contexto
        );
        $historicoEmail = $this->registrarHistorico($usuario, $evento, $contexto, $auditoriaEvento, $mailable);

        try {
            Mail::to($usuario->email)->send($mailable);
            $this->marcarAuditoriaComoEnviada($auditoriaEvento);
            $this->marcarHistoricoComoEnviado($historicoEmail);
        } catch (Throwable $e) {
            $this->marcarAuditoriaComoFalha($auditoriaEvento, $e);
            $this->marcarHistoricoComoFalha($historicoEmail, $e);

            Log::warning('Falha ao enviar notificacao de sistema.', [
                'evento' => $evento,
                'usuario_id' => $usuario->id,
                'erro' => $e->getMessage(),
            ]);
        }
    }

    private function podeEnviar(string $evento): bool
    {
        if (!config('notificacoes.sistema_ativa', false)) {
            return false;
        }

        if ($evento === 'aviso_admin') {
            return true;
        }

        $eventos = config('notificacoes.eventos_sistema_habilitados', []);

        return in_array($evento, is_array($eventos) ? $eventos : [], true);
    }

    private function normalizarContexto(string $evento, ?Usuario $ator, array $contexto): array
    {
        $contexto['protocolo'] = $contexto['protocolo'] ?? $this->gerarProtocolo($evento);

        if ($ator) {
            $contexto['responsavel_nome'] = $contexto['responsavel_nome'] ?? $ator->nome;
            $contexto['responsavel_funcao'] = $contexto['responsavel_funcao'] ?? $this->notificacaoSegurancaService->descreverFuncaoPublica($ator);
        }

        return $contexto;
    }

    private function gerarProtocolo(string $evento): string
    {
        $siglaEvento = Str::upper(Str::substr(preg_replace('/[^a-z]/i', '', $evento) ?: 'EV', 0, 3));

        return sprintf(
            'SIS-%s-%s-%s',
            now('America/Sao_Paulo')->format('YmdHis'),
            str_pad($siglaEvento, 3, 'X'),
            Str::upper(Str::random(4))
        );
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
                'categoria' => 'sistema',
                'ator_id' => $ator?->id,
                'ator_nome' => $ator?->nome,
                'ator_funcao' => $ator ? $this->notificacaoSegurancaService->descreverFuncaoPublica($ator) : null,
                'alvo_id' => $alvo->id,
                'alvo_nome' => $alvo->nome,
                'alvo_email' => $alvo->email,
                'igreja_id' => $contexto['igreja_id'] ?? null,
                'igreja_nome' => $contexto['igreja_nome'] ?? null,
                'contexto' => $contexto,
                'resultado' => 'registrado',
                'ip' => request()?->ip(),
                'user_agent' => request()?->userAgent(),
            ]);
        } catch (Throwable $e) {
            Log::warning('Falha ao registrar auditoria de sistema.', [
                'evento' => $evento,
                'alvo_id' => $alvo->id,
                'erro' => $e->getMessage(),
            ]);

            return null;
        }
    }

    private function registrarHistorico(
        Usuario $alvo,
        string $evento,
        array $contexto,
        ?AuditoriaEvento $auditoriaEvento,
        NotificacaoSistemaMail $mailable
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
                    'titulo' => $contexto['titulo'] ?? null,
                    'nome' => $contexto['nome'] ?? null,
                    'quantidade' => $contexto['quantidade'] ?? null,
                ],
            ]);
        } catch (Throwable $e) {
            Log::warning('Falha ao registrar historico de sistema.', [
                'evento' => $evento,
                'alvo_id' => $alvo->id,
                'erro' => $e->getMessage(),
            ]);

            return null;
        }
    }

    private function registrarHistoricoCancelado(
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
            Log::warning('Falha ao registrar historico cancelado de sistema.', [
                'evento' => $evento,
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
}
