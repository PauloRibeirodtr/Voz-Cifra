<?php

namespace App\Models;

use App\Enums\PapelIgreja;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class Igreja extends Model
{
    use HasFactory;

    protected $table = 'igrejas';

    protected $fillable = [
        'nome',
        'slug',
        'slug_publico_musicos',
        'cnpj',
        'telefone_secretaria',
        'cep',
        'endereco',
        'numero',
        'bairro',
        'cidade',
        'estado',
        'imagem_path',
        'status_operacional',
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

    public function usuariosLegados(): HasMany
    {
        return $this->usuarios();
    }

    public function vinculosUsuarios(): HasMany
    {
        return $this->hasMany(UsuarioIgreja::class, 'igreja_id');
    }

    public function usuariosVinculados(): BelongsToMany
    {
        return $this->belongsToMany(Usuario::class, 'usuario_igreja', 'igreja_id', 'usuario_id')
            ->withPivot(['id', 'ativo', 'responsavel_principal', 'vinculado_em', 'desvinculado_em']);
    }

    public function usuariosVinculadosAtivos(): BelongsToMany
    {
        return $this->usuariosVinculados()->wherePivot('ativo', true);
    }

    public function usuariosComPapel(PapelIgreja|string $papel): BelongsToMany
    {
        $papelEnum = PapelIgreja::fromValue($papel);

        return $this->usuariosVinculadosAtivos()
            ->whereExists(function ($query) use ($papelEnum): void {
                $query->selectRaw('1')
                    ->from('usuario_igreja_papeis')
                    ->whereColumn('usuario_igreja_papeis.usuario_igreja_id', 'usuario_igreja.id')
                    ->where('usuario_igreja_papeis.ativo', true)
                    ->whereNull('usuario_igreja_papeis.revogado_em')
                    ->where('usuario_igreja_papeis.papel', $papelEnum->value);
            });
    }

    public function usuariosComPapelAdminLocal(): BelongsToMany
    {
        return $this->usuariosComPapel(PapelIgreja::ADMIN_LOCAL);
    }

    public function coordenadores(): BelongsToMany
    {
        return $this->usuariosComPapel(PapelIgreja::COORDENADOR);
    }

    public function musicos(): BelongsToMany
    {
        return $this->usuariosComPapel(PapelIgreja::MUSICO);
    }

    public function listarUsuariosPorPapel(PapelIgreja|string $papel): Collection
    {
        return $this->usuariosComPapel($papel)->get();
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

    public function slugPublicoMusicos(): string
    {
        return trim((string) $this->slug_publico_musicos) ?: (string) $this->slug;
    }

    public function estaOperacional(): bool
    {
        return $this->status_operacional === 'operacional';
    }

    public function statusOperacionalLabel(): string
    {
        return match ($this->status_operacional) {
            'operacional' => 'Operacional',
            default => 'Aguardando admin local',
        };
    }

    public function imagemUrl(): string
    {
        $path = trim((string) $this->imagem_path);
        $disk = (string) config('filesystems.public_uploads_disk', config('filesystems.default'));

        if ($this->temImagemPersonalizada()) {
            $url = $disk === 'public'
                ? route('media.public.show', ['path' => $path], false)
                : Storage::disk($disk)->url($path);

            try {
                return $url . '?v=' . Storage::disk($disk)->lastModified($path);
            } catch (\Throwable) {
                return $url;
            }
        }

        return asset('logo/final.png');
    }

    public function temImagemPersonalizada(): bool
    {
        $path = trim((string) $this->imagem_path);
        $disk = (string) config('filesystems.public_uploads_disk', config('filesystems.default'));

        return $path !== '' && Storage::disk($disk)->exists($path);
    }
}
