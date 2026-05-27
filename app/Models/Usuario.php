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
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

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
        'foto_perfil_path',
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

    public function igrejaLegada(): BelongsTo
    {
        return $this->igreja();
    }

    public function igrejas(): HasMany
    {
        return $this->hasMany(UsuarioIgreja::class, 'usuario_id');
    }

    public function vinculosIgreja(): HasMany
    {
        return $this->hasMany(UsuarioIgreja::class, 'usuario_id');
    }

    public function vinculosIgrejaAtivos(): HasMany
    {
        return $this->vinculosIgreja()->where('ativo', true);
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

    public function papeisAtivosPorIgreja(): HasManyThrough
    {
        return $this->papeisPorIgreja()
            ->where('usuario_igreja_papeis.ativo', true)
            ->whereNull('usuario_igreja_papeis.revogado_em');
    }

    public function nivelGlobal(): int
    {
        $nivel = (int) ($this->nivel_global ?? 0);

        if ($nivel >= 1 && $nivel <= 6) {
            return $nivel;
        }

        return $this->ehAdminMaster() ? 6 : 1;
    }

    public function vinculoNaIgreja(Igreja|int|null $igreja = null): ?UsuarioIgreja
    {
        $igrejaId = $this->resolverIgrejaId($igreja);

        if ($igrejaId === null) {
            return null;
        }

        /** @var UsuarioIgreja|null $vinculo */
        $vinculo = $this->vinculosIgreja()
            ->where('igreja_id', $igrejaId)
            ->orderByDesc('ativo')
            ->orderByDesc('responsavel_principal')
            ->orderBy('id')
            ->first();

        return $vinculo;
    }

    public function vinculoPrincipal(): ?UsuarioIgreja
    {
        /** @var UsuarioIgreja|null $vinculo */
        $vinculo = $this->vinculosIgrejaAtivos()
            ->whereHas('papeisAtivos')
            ->orderByDesc('responsavel_principal')
            ->orderBy('id')
            ->first();

        return $vinculo;
    }

    public function listarPapeisNaIgreja(Igreja|int|null $igreja = null): Collection
    {
        $igrejaId = $this->resolverIgrejaId($igreja);

        if ($igrejaId !== null) {
            $vinculo = $this->vinculoNaIgreja($igrejaId);

            if ($vinculo instanceof UsuarioIgreja) {
                return $vinculo->listarPapeisAtivos();
            }

            return collect();
        }

        return $this->vinculosIgrejaAtivos()
            ->with('papeisAtivos')
            ->get()
            ->flatMap(fn (UsuarioIgreja $vinculo) => $vinculo->listarPapeisAtivos())
            ->unique(fn (PapelIgreja $papel) => $papel->value)
            ->values();
    }

    public function temPapelNaIgreja(PapelIgreja|string $papel, Igreja|int|null $igreja = null): bool
    {
        $papelEnum = PapelIgreja::fromValue($papel);

        return $this->listarPapeisNaIgreja($igreja)
            ->contains(fn (PapelIgreja $papelAtual) => $papelAtual === $papelEnum);
    }

    public function adicionarPapel(
        PapelIgreja|string $papel,
        Igreja|int $igreja,
        ?Usuario $ator = null,
        ?string $origem = null
    ): UsuarioIgrejaPapel {
        $igrejaId = $this->resolverIgrejaIdObrigatorio($igreja);

        return DB::transaction(function () use ($papel, $igrejaId, $ator, $origem): UsuarioIgrejaPapel {
            $vinculo = $this->obterOuCriarVinculoNaIgreja($igrejaId);
            $registro = $vinculo->adicionarPapel($papel, $ator, $origem);

            $this->sincronizarIgrejaLegadaPrincipal();

            return $registro;
        });
    }

    public function removerPapel(
        PapelIgreja|string $papel,
        Igreja|int $igreja,
        ?Usuario $ator = null
    ): ?UsuarioIgrejaPapel {
        $igrejaId = $this->resolverIgrejaIdObrigatorio($igreja);

        return DB::transaction(function () use ($papel, $igrejaId, $ator): ?UsuarioIgrejaPapel {
            $vinculo = $this->vinculoNaIgreja($igrejaId);

            if (!$vinculo instanceof UsuarioIgreja) {
                return null;
            }

            $registro = $vinculo->removerPapel($papel, $ator);

            if (!$vinculo->papeisAtivos()->exists()) {
                $vinculo->forceFill([
                    'ativo' => false,
                    'responsavel_principal' => false,
                    'desvinculado_em' => now(),
                ])->save();
            }

            $this->sincronizarIgrejaLegadaPrincipal();

            return $registro;
        });
    }

    public function possuiPapel(string $papel, ?int $igrejaId = null): bool
    {
        return $this->temPapelNaIgreja($papel, $igrejaId);
    }

    public function igrejaAtiva(): ?Igreja
    {
        /** @var IgrejaAtivaService $servico */
        $servico = app(IgrejaAtivaService::class);
        $igrejaAtiva = $servico->get();

        if ($igrejaAtiva !== null) {
            return $igrejaAtiva;
        }

        $vinculoPrincipal = $this->vinculoPrincipal();
        if ($vinculoPrincipal instanceof UsuarioIgreja) {
            return $vinculoPrincipal->igreja;
        }

        if ($this->igreja_id !== null) {
            return Igreja::find((int) $this->igreja_id);
        }

        return null;
    }

    public function igrejaAtivaId(): ?int
    {
        return $this->igrejaAtiva()?->id;
    }

    public function igrejasDisponiveisParaAtivacao(): Collection
    {
        return $this->vinculosIgrejaAtivos()
            ->with('igreja')
            ->get()
            ->pluck('igreja')
            ->filter(fn ($igreja) => $igreja instanceof Igreja)
            ->unique('id')
            ->values();
    }

    public function igrejasDisponiveisPorPapel(PapelIgreja|string $papel): Collection
    {
        $papelEnum = PapelIgreja::fromValue($papel);

        return $this->vinculosIgrejaAtivos()
            ->with(['igreja', 'papeisAtivos'])
            ->get()
            ->filter(fn (UsuarioIgreja $vinculo): bool => $vinculo->listarPapeisAtivos()->contains($papelEnum))
            ->pluck('igreja')
            ->filter(fn ($igreja) => $igreja instanceof Igreja)
            ->unique('id')
            ->values();
    }

    public function fotoPerfilUrl(): string
    {
        $path = trim((string) $this->foto_perfil_path);
        $disk = (string) config('filesystems.public_uploads_disk', config('filesystems.default'));

        if ($path !== '' && Storage::disk($disk)->exists($path)) {
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

    public function cpfMascarado(): string
    {
        $digitos = preg_replace('/\D+/', '', (string) $this->cpf);

        if (strlen($digitos) !== 11) {
            return 'CPF nao informado';
        }

        return substr($digitos, 0, 3) . '.***.***-' . substr($digitos, -2);
    }

    public function telefoneMascarado(): string
    {
        $digitos = preg_replace('/\D+/', '', (string) $this->telefone);

        if (strlen($digitos) < 10) {
            return 'Telefone nao informado';
        }

        $ddd = substr($digitos, 0, 2);
        $final = substr($digitos, -4);

        return '(' . $ddd . ') *****-' . $final;
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

    public function historicoEnviosEmail(): HasMany
    {
        return $this->hasMany(HistoricoEnvioEmail::class, 'usuario_id');
    }

    public function notificacoesInternas(): HasMany
    {
        return $this->hasMany(NotificacaoInterna::class, 'usuario_id');
    }

    public function ehAdminMaster(): bool
    {
        return $this->perfil_global === 'admin_master' || (int) ($this->nivel_global ?? 0) >= 6;
    }

    public function ehAdminLocal(): bool
    {
        $igrejaAtivaId = $this->igrejaAtiva()?->id;

        if ($igrejaAtivaId !== null) {
            return $this->temPapelNaIgreja(PapelIgreja::ADMIN_LOCAL, $igrejaAtivaId);
        }

        return $this->temPapelNaIgreja(PapelIgreja::ADMIN_LOCAL);
    }

    public function ehCoordenador(): bool
    {
        $igrejaAtivaId = $this->igrejaAtiva()?->id;

        if ($igrejaAtivaId !== null) {
            return $this->temPapelNaIgreja(PapelIgreja::COORDENADOR, $igrejaAtivaId);
        }

        return $this->temPapelNaIgreja(PapelIgreja::COORDENADOR);
    }

    public function ehMembro(): bool
    {
        $igrejaAtivaId = $this->igrejaAtiva()?->id;

        if ($igrejaAtivaId !== null) {
            return $this->temPapelNaIgreja(PapelIgreja::MUSICO, $igrejaAtivaId)
                || $this->temPapelNaIgreja(PapelIgreja::COORDENADOR, $igrejaAtivaId)
                || $this->temPapelNaIgreja(PapelIgreja::ADMIN_LOCAL, $igrejaAtivaId);
        }

        return $this->temPapelNaIgreja(PapelIgreja::MUSICO)
            || $this->temPapelNaIgreja(PapelIgreja::COORDENADOR)
            || $this->temPapelNaIgreja(PapelIgreja::ADMIN_LOCAL);
    }

    public function ehUsuarioGlobal(): bool
    {
        return $this->perfil_global === 'usuario';
    }

    public function ehPadre(): bool
    {
        return (bool) $this->eh_padre;
    }

    public function rotaDestinoAposLogin(): ?string
    {
        if ($this->ehAdminMaster()) {
            return 'admin.dashboard';
        }

        if ($this->ehAdminLocal()) {
            return 'local-admin.dashboard';
        }

        if ($this->ehCoordenador()) {
            return 'coordenador.dashboard';
        }

        if ($this->ehMembro()) {
            return 'member.dashboard';
        }

        return null;
    }

    public function rotaDestinoPrimeiroAcesso(): ?string
    {
        if ($this->ehAdminMaster()) {
            return 'admin.profile';
        }

        if ($this->ehAdminLocal()) {
            return 'local-admin.profile';
        }

        if ($this->ehCoordenador()) {
            return 'coordenador.profile';
        }

        if ($this->ehMembro()) {
            return 'member.profile';
        }

        return null;
    }

    public function mensagemPrimeiroAcesso(): string
    {
        if ($this->ehAdminMaster()) {
            return 'No primeiro acesso, atualize sua senha para continuar usando o sistema com seguranca.';
        }

        if ($this->ehAdminLocal()) {
            return 'No primeiro acesso, atualize sua senha para continuar usando o painel da igreja com seguranca.';
        }

        if ($this->ehCoordenador()) {
            return 'No primeiro acesso, atualize sua senha para liberar seu acesso operacional com seguranca.';
        }

        return 'No primeiro acesso, atualize sua senha para liberar o painel musical com seguranca.';
    }

    public function garantirVinculoNaIgreja(Igreja|int $igreja): UsuarioIgreja
    {
        return DB::transaction(function () use ($igreja): UsuarioIgreja {
            $vinculo = $this->obterOuCriarVinculoNaIgreja($igreja);

            $this->sincronizarIgrejaLegadaPrincipal();

            return $vinculo;
        });
    }

    protected function obterOuCriarVinculoNaIgreja(Igreja|int $igreja): UsuarioIgreja
    {
        $igrejaId = $this->resolverIgrejaIdObrigatorio($igreja);
        $vinculoExistente = $this->vinculoNaIgreja($igrejaId);

        if ($vinculoExistente instanceof UsuarioIgreja) {
            $vinculoExistente->fill([
                'ativo' => true,
                'desvinculado_em' => null,
            ]);

            if (!$this->vinculosIgrejaAtivos()->whereHas('papeisAtivos')->where('responsavel_principal', true)->exists()) {
                $vinculoExistente->responsavel_principal = true;
            }

            $vinculoExistente->save();

            return $vinculoExistente->refresh();
        }

        return $this->vinculosIgreja()->create([
            'igreja_id' => $igrejaId,
            'ativo' => true,
            'responsavel_principal' => !$this->vinculosIgrejaAtivos()->whereHas('papeisAtivos')->where('responsavel_principal', true)->exists(),
            'vinculado_em' => now(),
            'desvinculado_em' => null,
        ]);
    }

    protected function sincronizarIgrejaLegadaPrincipal(): void
    {
        $igrejaPrincipalId = $this->vinculosIgrejaAtivos()
            ->whereHas('papeisAtivos')
            ->orderByDesc('responsavel_principal')
            ->orderBy('id')
            ->value('igreja_id');

        $igrejaPrincipalId = is_numeric($igrejaPrincipalId) ? (int) $igrejaPrincipalId : null;

        if ($igrejaPrincipalId !== (int) ($this->igreja_id ?? 0)) {
            $this->forceFill([
                'igreja_id' => $igrejaPrincipalId,
            ])->save();
        }
    }

    protected function resolverIgrejaId(Igreja|int|null $igreja = null): ?int
    {
        if ($igreja instanceof Igreja) {
            return (int) $igreja->id;
        }

        if (is_int($igreja) && $igreja > 0) {
            return $igreja;
        }

        return null;
    }

    protected function resolverIgrejaIdObrigatorio(Igreja|int $igreja): int
    {
        $igrejaId = $this->resolverIgrejaId($igreja);

        if ($igrejaId === null) {
            throw new \InvalidArgumentException('Igreja invalida para operacao de papel.');
        }

        return $igrejaId;
    }
}
