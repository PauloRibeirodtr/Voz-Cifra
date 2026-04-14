<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Chamado extends Model
{
    use HasFactory;

    protected $table = 'chamados';

    protected $fillable = [
        'protocolo',
        'auditoria_evento_id',
        'titulo',
        'descricao',
        'status',
        'prioridade',
        'categoria',
        'canal_origem',
        'origem_tipo',
        'origem_id',
        'solicitante_usuario_id',
        'solicitante_nome',
        'solicitante_email',
        'solicitante_telegram_chat_id',
        'responsavel_usuario_id',
        'igreja_id',
        'igreja_nome',
        'ultima_interacao_em',
        'resolvido_em',
        'fechado_em',
        'resolucao_resumo',
        'avaliacao_nota',
        'avaliacao_comentario',
    ];

    protected function casts(): array
    {
        return [
            'ultima_interacao_em' => 'datetime',
            'resolvido_em' => 'datetime',
            'fechado_em' => 'datetime',
            'avaliacao_nota' => 'integer',
        ];
    }

    public function auditoriaEvento(): BelongsTo
    {
        return $this->belongsTo(AuditoriaEvento::class, 'auditoria_evento_id');
    }

    public function solicitante(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'solicitante_usuario_id');
    }

    public function responsavel(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'responsavel_usuario_id');
    }

    public function igreja(): BelongsTo
    {
        return $this->belongsTo(Igreja::class, 'igreja_id');
    }

    public function mensagens(): HasMany
    {
        return $this->hasMany(ChamadoMensagem::class, 'chamado_id');
    }
}
