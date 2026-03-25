<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Padre extends Model
{
    use HasFactory;

    protected $table = 'padres';

    protected $fillable = [
        'nome',
        'cpf',
        'ativo',
    ];

    protected function casts(): array
    {
        return [
            'ativo' => 'boolean',
        ];
    }

    public function missas(): HasMany
    {
        return $this->hasMany(Missa::class, 'padre_id');
    }
}
