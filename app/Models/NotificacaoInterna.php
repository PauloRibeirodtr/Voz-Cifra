<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificacaoInterna extends Model
{
    use HasFactory;

    protected $table = 'notificacoes_internas';

    protected $fillable = [
        'usuario_id',
        'ator_id',
        'igreja_id',
        'tipo',
        'titulo',
        'mensagem',
        'url',
        'dados',
        'lida_em',
    ];

    protected function casts(): array
    {
        return [
            'dados' => 'array',
            'lida_em' => 'datetime',
        ];
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function ator(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'ator_id');
    }

    public function igreja(): BelongsTo
    {
        return $this->belongsTo(Igreja::class, 'igreja_id');
    }

    public function scopeNaoLidas($query)
    {
        return $query->whereNull('lida_em');
    }
}
