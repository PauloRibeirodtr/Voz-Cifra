<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VersaoMusical extends Model
{
    use HasFactory;

    protected $table = 'versoes_musicais';

    protected $fillable = [
        'musica_id',
        'melodia_id',
        'titulo',
        'tom_musical',
        'bpm',
        'youtube_video_id',
        'letra_com_cifras',
        'criado_por',
        'ativo',
    ];

    protected function casts(): array
    {
        return [
            'bpm' => 'integer',
            'ativo' => 'boolean',
        ];
    }

    public function musica(): BelongsTo
    {
        return $this->belongsTo(Musica::class, 'musica_id');
    }

    public function melodia(): BelongsTo
    {
        return $this->belongsTo(Melodia::class, 'melodia_id');
    }

    public function criadoPor(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'criado_por');
    }

    public function missaMusicas(): HasMany
    {
        return $this->hasMany(MissaMusica::class, 'versao_musical_id');
    }
}
