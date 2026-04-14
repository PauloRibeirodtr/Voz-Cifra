<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChamadoMensagem extends Model
{
    use HasFactory;

    protected $table = 'chamado_mensagens';

    protected $fillable = [
        'chamado_id',
        'autor_usuario_id',
        'autor_nome',
        'origem',
        'canal',
        'interno',
        'mensagem',
    ];

    protected function casts(): array
    {
        return [
            'interno' => 'boolean',
        ];
    }

    public function chamado(): BelongsTo
    {
        return $this->belongsTo(Chamado::class, 'chamado_id');
    }

    public function autor(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'autor_usuario_id');
    }
}
