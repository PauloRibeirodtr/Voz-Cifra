<?php

namespace App\Models;

use App\Enums\PapelIgreja;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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
        'numero',
        'bairro',
        'cidade',
        'estado',
        'imagem_path',
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

    public function adminsLocais(): BelongsToMany
    {
        return $this->usuariosComPapelAdminLocal();
    }

    public function vinculosUsuarios(): HasMany
    {
        return $this->hasMany(UsuarioIgreja::class, 'igreja_id');
    }

    public function usuariosComPapelAdminLocal(): BelongsToMany
    {
        return $this->belongsToMany(Usuario::class, 'usuario_igreja', 'igreja_id', 'usuario_id')
            ->withPivot(['id', 'ativo', 'responsavel_principal', 'vinculado_em', 'desvinculado_em'])
            ->wherePivot('ativo', true)
            ->whereExists(function ($query): void {
                $query->selectRaw('1')
                    ->from('usuario_igreja_papeis')
                    ->whereColumn('usuario_igreja_papeis.usuario_igreja_id', 'usuario_igreja.id')
                    ->where('usuario_igreja_papeis.ativo', true)
                    ->where('usuario_igreja_papeis.papel', PapelIgreja::ADMIN_LOCAL->value);
            });
    }

    public function missas(): HasMany
    {
        return $this->hasMany(Missa::class, 'igreja_id');
    }

    public function celebrantes(): HasMany
    {
        return $this->hasMany(Usuario::class, 'igreja_id')
            ->where('eh_padre', true);
    }
}
