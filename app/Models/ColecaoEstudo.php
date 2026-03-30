<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ColecaoEstudo extends Model
{
    use HasFactory;

    protected $table = 'colecoes_estudo';

    protected $fillable = [
        'usuario_id',
        'nome',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function itens(): HasMany
    {
        return $this->hasMany(ColecaoEstudoItem::class, 'colecao_estudo_id');
    }
}
