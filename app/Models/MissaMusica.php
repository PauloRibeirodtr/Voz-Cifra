<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MissaMusica extends Model
{
    use HasFactory;

    protected $table = 'missa_musicas';

    protected $fillable = [
        'missa_id',
        'musica_id',
        'versao_musical_id',
        'momento_liturgico_id',
        'ordem',
    ];

    protected function casts(): array
    {
        return [
            'ordem' => 'integer',
        ];
    }

    public function missa(): BelongsTo
    {
        return $this->belongsTo(Missa::class, 'missa_id');
    }

    public function musica(): BelongsTo
    {
        return $this->belongsTo(Musica::class, 'musica_id');
    }

    public function versaoMusical(): BelongsTo
    {
        return $this->belongsTo(VersaoMusical::class, 'versao_musical_id');
    }

    public function momentoLiturgico(): BelongsTo
    {
        return $this->belongsTo(MomentoLiturgico::class, 'momento_liturgico_id');
    }
}
