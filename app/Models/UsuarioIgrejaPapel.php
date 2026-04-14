<?php

namespace App\Models;

use App\Enums\PapelIgreja;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UsuarioIgrejaPapel extends Model
{
    use HasFactory;

    protected $table = 'usuario_igreja_papeis';

    protected $fillable = [
        'usuario_igreja_id',
        'papel',
        'ativo',
        'concedido_por',
        'concedido_em',
    ];

    protected function casts(): array
    {
        return [
            'papel' => PapelIgreja::class,
            'ativo' => 'boolean',
            'concedido_em' => 'datetime',
        ];
    }

    public function vinculo(): BelongsTo
    {
        return $this->belongsTo(UsuarioIgreja::class, 'usuario_igreja_id');
    }

    public function concedidoPor(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'concedido_por');
    }
}
