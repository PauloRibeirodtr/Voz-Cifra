<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TempoLiturgico extends Model
{
    use HasFactory;

    protected $table = 'classificacoes_liturgicas';

    protected $fillable = [
        'tipo',
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

    protected static function booted(): void
    {
        static::addGlobalScope('tipo_tempo', fn (Builder $builder) => $builder->where('tipo', 'tempo'));

        static::creating(function (TempoLiturgico $tempoLiturgico): void {
            $tempoLiturgico->tipo = 'tempo';
        });
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
