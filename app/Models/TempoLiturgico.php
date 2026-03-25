<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TempoLiturgico extends Model
{
    use HasFactory;

    protected $table = 'tempos_liturgicos';

    protected $fillable = [
        'nome',
        'descricao',
        'ativo',
    ];

    protected function casts(): array
    {
        return [
            'ativo' => 'boolean',
        ];
    }

    public function musicas(): HasMany
    {
        return $this->hasMany(Musica::class, 'tempo_liturgico_id');
    }

    public function missas(): HasMany
    {
        return $this->hasMany(Missa::class, 'tempo_liturgico_id');
    }
}
