<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditoriaEvento extends Model
{
    use HasFactory;

    protected $table = 'auditoria_eventos';

    protected $fillable = [
        'protocolo',
        'evento',
        'categoria',
        'ator_id',
        'ator_nome',
        'ator_funcao',
        'alvo_id',
        'alvo_nome',
        'alvo_email',
        'igreja_id',
        'igreja_nome',
        'contexto',
        'resultado',
        'notificacao_enviada_em',
        'erro_envio',
        'ip',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'contexto' => 'array',
            'notificacao_enviada_em' => 'datetime',
        ];
    }

    public function ator(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'ator_id');
    }

    public function alvo(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'alvo_id');
    }

    public function igreja(): BelongsTo
    {
        return $this->belongsTo(Igreja::class, 'igreja_id');
    }
}
