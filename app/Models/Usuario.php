<?php

namespace App\Models;

use App\Enums\PapelIgreja;
use App\Services\IgrejaAtivaService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
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
        'nivel_global',
        'eh_padre',
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
            'nivel_global' => 'integer',
            'eh_padre' => 'boolean',
            'password' => 'hashed',
        ];
    }

    public function igreja(): BelongsTo
    {
        return $this->belongsTo(Igreja::class, 'igreja_id');
    }

    public function igrejas(): HasMany
    {
        return $this->hasMany(UsuarioIgreja::class, 'usuario_id');
    }

    public function vinculosIgreja(): HasMany
    {
        return $this->hasMany(UsuarioIgreja::class, 'usuario_id');
    }

    public function papeisPorIgreja(): HasManyThrough
    {
        return $this->hasManyThrough(
            UsuarioIgrejaPapel::class,
            UsuarioIgreja::class,
            'usuario_id',
            'usuario_igreja_id',
            'id',
            'id'
        );
    }

    public function nivelGlobal(): int
    {
        $nivel = (int) ($this->nivel_global ?? 0);

        if ($nivel >= 1 && $nivel <= 7) {
            return $nivel;
        }

        return $this->ehAdminMaster() ? 6 : 1;
    }

    public function possuiPapel(string $papel, ?int $igrejaId = null): bool
    {
        $papel = trim($papel);

        if ($papel === '' || !in_array($papel, PapelIgreja::values(), true)) {
            return false;
        }

        $consulta = $this->papeisPorIgreja()
            ->where('papel', $papel)
            ->where('ativo', true)
            ->whereHas('vinculo', function ($query) use ($igrejaId): void {
                $query->where('ativo', true);

                if ($igrejaId !== null) {
                    $query->where('igreja_id', $igrejaId);
                }
            });

        if ($consulta->exists()) {
            return true;
        }

        $papelLegado = match ($this->perfil_global) {
            'admin_local' => PapelIgreja::ADMIN_LOCAL->value,
            'member' => PapelIgreja::MUSICO->value,
            default => null,
        };

        if ($papelLegado !== $papel) {
            return false;
        }

        if ($igrejaId === null) {
            return $this->igreja_id !== null;
        }

        return (int) $this->igreja_id === (int) $igrejaId;
    }

    public function igrejaAtiva(): ?Igreja
    {
        /** @var IgrejaAtivaService $servico */
        $servico = app(IgrejaAtivaService::class);
        $igrejaAtiva = $servico->get();

        if ($igrejaAtiva !== null) {
            return $igrejaAtiva;
        }

        if ($this->igreja_id === null) {
            return null;
        }

        return Igreja::find((int) $this->igreja_id);
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
        if ($this->perfil_global === 'admin_local') {
            return true;
        }

        return $this->possuiPapel(PapelIgreja::ADMIN_LOCAL->value, $this->igrejaAtiva()?->id);
    }

    public function ehCoordenador(): bool
    {
        return $this->possuiPapel(PapelIgreja::COORDENADOR->value, $this->igrejaAtiva()?->id);
    }

    public function ehMembro(): bool
    {
        if ($this->perfil_global === 'member') {
            return true;
        }

        return $this->possuiPapel(PapelIgreja::MUSICO->value, $this->igrejaAtiva()?->id)
            || $this->ehCoordenador();
    }

    public function ehUsuarioGlobal(): bool
    {
        return $this->perfil_global === 'usuario';
    }
}
