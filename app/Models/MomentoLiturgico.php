<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MomentoLiturgico extends Model
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
        static::addGlobalScope('tipo_momento', fn (Builder $builder) => $builder->where('tipo', 'momento'));

        static::creating(function (MomentoLiturgico $momentoLiturgico): void {
            $momentoLiturgico->tipo = 'momento';
        });
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
