<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Melodia extends Model
{
    use HasFactory;

    protected $table = 'melodias';

    protected $fillable = [
        'musica_id',
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

    public function musica(): BelongsTo
    {
        return $this->belongsTo(Musica::class, 'musica_id');
    }

    public function versoesMusicais(): HasMany
    {
        return $this->hasMany(VersaoMusical::class, 'melodia_id');
    }
}
