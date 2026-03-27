<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Igreja extends Model
{
    use HasFactory;

    protected $table = 'igrejas';

    protected $fillable = [
        'nome',
        'slug',
        'cnpj',
        'cep',
        'endereco',
        'cidade',
        'estado',
        'ativo',
    ];

    protected function casts(): array
    {
        return [
            'ativo' => 'boolean',
        ];
    }

    public function usuarios(): HasMany
    {
        return $this->hasMany(Usuario::class, 'igreja_id');
    }

    public function missas(): HasMany
    {
        return $this->hasMany(Missa::class, 'igreja_id');
    }

    public function padres(): HasMany
    {
        return $this->hasMany(Padre::class, 'igreja_id');
    }
}
