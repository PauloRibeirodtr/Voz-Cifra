<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
}
