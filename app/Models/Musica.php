<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Musica extends Model
{
    use HasFactory;

    protected $table = 'musicas';

    protected $fillable = [
        'titulo',
        'artista',
        'letra',
        'momento_liturgico_id',
        'tempo_liturgico_id',
        'criado_por',
        'ativo',
    ];

    protected function casts(): array
    {
        return [
            'ativo' => 'boolean',
        ];
    }

    public function momentoLiturgico(): BelongsTo
    {
        return $this->belongsTo(MomentoLiturgico::class, 'momento_liturgico_id');
    }

    public function tempoLiturgico(): BelongsTo
    {
        return $this->belongsTo(TempoLiturgico::class, 'tempo_liturgico_id');
    }

    public function criadoPor(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'criado_por');
    }

    public function versoesMusicais(): HasMany
    {
        return $this->hasMany(VersaoMusical::class, 'musica_id');
    }

    public function missaMusicas(): HasMany
    {
        return $this->hasMany(MissaMusica::class, 'musica_id');
    }
}
