<?php

namespace App\Models;

use App\Enums\PapelIgreja;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class UsuarioIgreja extends Model
{
    use HasFactory;

    protected $table = 'usuario_igreja';

    protected $fillable = [
        'usuario_id',
        'igreja_id',
        'ativo',
        'responsavel_principal',
        'vinculado_em',
        'desvinculado_em',
    ];

    protected function casts(): array
    {
        return [
            'ativo' => 'boolean',
            'responsavel_principal' => 'boolean',
            'vinculado_em' => 'datetime',
            'desvinculado_em' => 'datetime',
        ];
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function igreja(): BelongsTo
    {
        return $this->belongsTo(Igreja::class, 'igreja_id');
    }

    public function papeis(): HasMany
    {
        return $this->hasMany(UsuarioIgrejaPapel::class, 'usuario_igreja_id');
    }

    public function papeisAtivos(): HasMany
    {
        return $this->papeis()->ativos();
    }

    public function listarPapeisAtivos(): Collection
    {
        $papeis = $this->relationLoaded('papeisAtivos')
            ? $this->getRelation('papeisAtivos')
            : $this->papeisAtivos()->get();

        return $papeis
            ->map(fn (UsuarioIgrejaPapel $papel) => $papel->papel instanceof PapelIgreja
                ? $papel->papel
                : PapelIgreja::fromValue((string) $papel->papel))
            ->values();
    }

    public function temPapel(PapelIgreja|string $papel): bool
    {
        return $this->papeisAtivos()
            ->doPapel($papel)
            ->exists();
    }

    public function adicionarPapel(
        PapelIgreja|string $papel,
        ?Usuario $ator = null,
        ?string $origem = null
    ): UsuarioIgrejaPapel {
        $papelEnum = PapelIgreja::fromValue($papel);

        /** @var UsuarioIgrejaPapel|null $registroExistente */
        $registroExistente = $this->papeis()
            ->where('papel', $papelEnum->value)
            ->first();

        if ($registroExistente instanceof UsuarioIgrejaPapel) {
            $registroExistente->fill([
                'ativo' => true,
                'origem' => $origem ?: $registroExistente->origem,
                'concedido_por' => $ator?->id ?? $registroExistente->concedido_por,
                'concedido_em' => $registroExistente->concedido_em ?? now(),
                'revogado_por' => null,
                'revogado_em' => null,
            ]);
            $registroExistente->save();

            return $registroExistente->refresh();
        }

        return $this->papeis()->create([
            'papel' => $papelEnum->value,
            'ativo' => true,
            'origem' => $origem,
            'concedido_por' => $ator?->id,
            'concedido_em' => now(),
            'revogado_por' => null,
            'revogado_em' => null,
        ]);
    }

    public function removerPapel(
        PapelIgreja|string $papel,
        ?Usuario $ator = null
    ): ?UsuarioIgrejaPapel {
        /** @var UsuarioIgrejaPapel|null $registro */
        $registro = $this->papeisAtivos()
            ->doPapel($papel)
            ->first();

        if (!$registro instanceof UsuarioIgrejaPapel) {
            return null;
        }

        $registro->fill([
            'ativo' => false,
            'revogado_por' => $ator?->id,
            'revogado_em' => now(),
        ]);
        $registro->save();

        return $registro->refresh();
    }
}
