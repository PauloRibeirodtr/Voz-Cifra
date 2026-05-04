<?php

namespace App\Mail;

use App\Models\Usuario;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ConviteAcessoInicialMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Usuario $alvo,
        public readonly ?Usuario $ator = null,
        public readonly array $contexto = []
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Convite de acesso ao Voz & Cifra'
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.acesso-inicial.convite'
        );
    }
}
