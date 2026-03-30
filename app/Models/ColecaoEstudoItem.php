<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ColecaoEstudoItem extends Model
{
    use HasFactory;

    protected $table = 'colecao_estudo_itens';

    protected $fillable = [
        'colecao_estudo_id',
        'musica_id',
        'versao_musical_id',
    ];

    public function colecao(): BelongsTo
    {
        return $this->belongsTo(ColecaoEstudo::class, 'colecao_estudo_id');
    }

    public function musica(): BelongsTo
    {
        return $this->belongsTo(Musica::class, 'musica_id');
    }

    public function versaoMusical(): BelongsTo
    {
        return $this->belongsTo(VersaoMusical::class, 'versao_musical_id');
    }
}
