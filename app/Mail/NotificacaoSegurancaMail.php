<?php

namespace App\Mail;

use App\Models\Usuario;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NotificacaoSegurancaMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $evento,
        public readonly Usuario $alvo,
        public readonly ?Usuario $ator = null,
        public readonly array $contexto = []
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->assunto()
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.seguranca.notificacao'
        );
    }

    private function assunto(): string
    {
        $assuntoBase = match ($this->evento) {
            'reset_senha' => 'Aviso de seguranca: senha redefinida',
            'conta_inativada' => 'Aviso de seguranca: conta inativada',
            'conta_reativada' => 'Aviso de seguranca: conta reativada',
            'troca_nivel_global' => 'Aviso de seguranca: nivel global alterado',
            'papel_local_concedido' => 'Aviso de seguranca: papel por igreja concedido',
            'papel_local_revogado' => 'Aviso de seguranca: papel por igreja revogado',
            default => 'Aviso de seguranca da conta',
        };

        $protocolo = $this->contexto['protocolo'] ?? null;

        if (!is_string($protocolo) || trim($protocolo) === '') {
            return $assuntoBase;
        }

        return $assuntoBase . ' [' . trim($protocolo) . ']';
    }
}
