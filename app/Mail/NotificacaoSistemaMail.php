<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NotificacaoSistemaMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $evento,
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
            view: 'emails.sistema.notificacao'
        );
    }

    private function assunto(): string
    {
        $assuntoBase = match ($this->evento) {
            'musica_cadastrada' => 'Nova musica cadastrada no sistema',
            'acorde_cadastrado' => 'Novo acorde cadastrado no sistema',
            'musica_inativada' => 'Musica inativada no sistema',
            'acorde_inativado' => 'Acorde inativado no sistema',
            'acordes_marco_alcancado' => 'Marco de acordes atingido no sistema',
            'aviso_admin' => (string) ($this->contexto['titulo'] ?? 'Aviso do Voz & Cifra'),
            default => 'Atualizacao do sistema',
        };

        $protocolo = $this->contexto['protocolo'] ?? null;

        if (!is_string($protocolo) || trim($protocolo) === '') {
            return $assuntoBase;
        }

        return $assuntoBase . ' [' . trim($protocolo) . ']';
    }
}
