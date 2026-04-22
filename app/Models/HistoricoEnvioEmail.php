<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HistoricoEnvioEmail extends Model
{
    use HasFactory;

    protected $table = 'historico_envios_email';

    protected $fillable = [
        'usuario_id',
        'auditoria_evento_id',
        'origem_tipo',
        'origem_id',
        'destinatario_email',
        'destinatario_nome',
        'tipo_email',
        'assunto',
        'status_envio',
        'mensagem_retorno',
        'mensagem_id_provedor',
        'mailer',
        'payload',
        'enviado_em',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'enviado_em' => 'datetime',
        ];
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function auditoriaEvento(): BelongsTo
    {
        return $this->belongsTo(AuditoriaEvento::class, 'auditoria_evento_id');
    }
}
