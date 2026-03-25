<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MomentoLiturgico extends Model
{
    use HasFactory;

    protected $table = 'momentos_liturgicos';

    protected $fillable = [
        'nome',
        'descricao',
        'ordem_exibicao',
        'ativo',
    ];

    protected function casts(): array
    {
        return [
            'ordem_exibicao' => 'integer',
            'ativo' => 'boolean',
        ];
    }

    public function musicas(): HasMany
    {
        return $this->hasMany(Musica::class, 'momento_liturgico_id');
    }

    public function missaMusicas(): HasMany
    {
        return $this->hasMany(MissaMusica::class, 'momento_liturgico_id');
    }
}
