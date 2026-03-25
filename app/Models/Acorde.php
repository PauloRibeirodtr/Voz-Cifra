<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Acorde extends Model
{
    use HasFactory;

    protected $table = 'acordes';

    protected $fillable = [
        'nome',
        'descricao',
        'dados_diagrama',
        'ativo',
    ];

    protected function casts(): array
    {
        return [
            'dados_diagrama' => 'array',
            'ativo' => 'boolean',
        ];
    }
}
