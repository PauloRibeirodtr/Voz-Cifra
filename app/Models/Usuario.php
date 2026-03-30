<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Usuario extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'usuarios';

    protected $fillable = [
        'igreja_id',
        'nome',
        'cpf',
        'email',
        'telefone',
        'password',
        'perfil_global',
        'ativo',
        'primeiro_acesso',
        'theme_preference',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'ativo' => 'boolean',
            'primeiro_acesso' => 'boolean',
            'password' => 'hashed',
        ];
    }

    public function igreja(): BelongsTo
    {
        return $this->belongsTo(Igreja::class, 'igreja_id');
    }

    public function musicasCriadas(): HasMany
    {
        return $this->hasMany(Musica::class, 'criado_por');
    }

    public function versoesMusicaisCriadas(): HasMany
    {
        return $this->hasMany(VersaoMusical::class, 'criado_por');
    }

    public function colecoesEstudo(): HasMany
    {
        return $this->hasMany(ColecaoEstudo::class, 'usuario_id');
    }

    public function ehAdminMaster(): bool
    {
        return $this->perfil_global === 'admin_master';
    }

    public function ehAdminLocal(): bool
    {
        return $this->perfil_global === 'admin_local';
    }

    public function ehMembro(): bool
    {
        return $this->perfil_global === 'member';
    }
}
