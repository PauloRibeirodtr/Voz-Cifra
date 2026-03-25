<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Missa extends Model
{
    use HasFactory;

    protected $table = 'missas';

    protected $fillable = [
        'igreja_id',
        'padre_id',
        'tempo_liturgico_id',
        'titulo',
        'data_missa',
        'hora_inicio',
        'hora_fim',
        'observacoes',
        'ativo',
    ];

    protected function casts(): array
    {
        return [
            'data_missa' => 'date',
            'ativo' => 'boolean',
        ];
    }

    public function igreja(): BelongsTo
    {
        return $this->belongsTo(Igreja::class, 'igreja_id');
    }

    public function padre(): BelongsTo
    {
        return $this->belongsTo(Padre::class, 'padre_id');
    }

    public function tempoLiturgico(): BelongsTo
    {
        return $this->belongsTo(TempoLiturgico::class, 'tempo_liturgico_id');
    }

    public function missaMusicas(): HasMany
    {
        return $this->hasMany(MissaMusica::class, 'missa_id');
    }
}
