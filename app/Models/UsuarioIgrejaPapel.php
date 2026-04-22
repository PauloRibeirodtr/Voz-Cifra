<?php

namespace App\Models;

use App\Enums\PapelIgreja;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class UsuarioIgrejaPapel extends Model
{
    use HasFactory;

    protected $table = 'usuario_igreja_papeis';

    protected $fillable = [
        'usuario_igreja_id',
        'papel',
        'ativo',
        'origem',
        'concedido_por',
        'revogado_por',
        'concedido_em',
        'revogado_em',
    ];

    protected function casts(): array
    {
        return [
            'papel' => PapelIgreja::class,
            'ativo' => 'boolean',
            'concedido_em' => 'datetime',
            'revogado_em' => 'datetime',
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

    public function revogadoPor(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'revogado_por');
    }

    public function scopeAtivos(Builder $query): Builder
    {
        return $query
            ->where('ativo', true)
            ->whereNull('revogado_em');
    }

    public function scopeDoPapel(Builder $query, PapelIgreja|string $papel): Builder
    {
        return $query->where('papel', PapelIgreja::fromValue($papel)->value);
    }

    public function estaAtivo(): bool
    {
        return (bool) $this->ativo && $this->revogado_em === null;
    }
}
