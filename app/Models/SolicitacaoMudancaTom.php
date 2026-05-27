<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SolicitacaoMudancaTom extends Model
{
    use HasFactory;

    public const STATUS_PENDENTE = 'pendente';
    public const STATUS_APROVADA = 'aprovada';
    public const STATUS_RECUSADA = 'recusada';

    protected $table = 'solicitacoes_mudanca_tom';

    protected $fillable = [
        'missa_musica_id',
        'missa_id',
        'igreja_id',
        'usuario_id',
        'tom_atual',
        'tom_sugerido',
        'observacao',
        'status',
        'resposta',
        'revisado_por',
        'revisado_em',
    ];

    protected function casts(): array
    {
        return [
            'revisado_em' => 'datetime',
        ];
    }

    public function missaMusica(): BelongsTo
    {
        return $this->belongsTo(MissaMusica::class, 'missa_musica_id');
    }

    public function missa(): BelongsTo
    {
        return $this->belongsTo(Missa::class, 'missa_id');
    }

    public function igreja(): BelongsTo
    {
        return $this->belongsTo(Igreja::class, 'igreja_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function revisor(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'revisado_por');
    }

    public function estaPendente(): bool
    {
        return $this->status === self::STATUS_PENDENTE;
    }
}
