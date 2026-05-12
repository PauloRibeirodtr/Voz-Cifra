<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PapelIgreja;
use App\Http\Controllers\Controller;
use App\Models\Igreja;
use App\Models\Usuario;
use App\Models\UsuarioIgrejaPapel;
use App\Services\GestaoUsuariosIgrejaService;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class UsuarioController extends Controller
{
    public function __construct(
        private readonly GestaoUsuariosIgrejaService $gestaoUsuariosIgrejaService
    ) {
    }

    public function index(Request $request): View
    {
        $buscaOriginal = trim((string) $request->string('q'));
        $busca = $this->normalizarBusca($buscaOriginal);
        $deveFiltrarBusca = mb_strlen($busca) >= 3;
        $status = trim((string) $request->string('status'));
        $tipo = trim((string) $request->string('tipo'));
        $presenca = trim((string) $request->string('presenca'));
        $limiteOnline = now()->subMinutes(5)->timestamp;
        $presencas = $this->obterPresencasUsuarios();
        $usuariosOnlineIds = $presencas
            ->filter(fn (int $lastActivity): bool => $lastActivity >= $limiteOnline)
            ->keys()
            ->map(fn ($id): int => (int) $id)
            ->values();

        $usuarios = Usuario::query()
            ->with(['vinculosIgreja.igreja', 'vinculosIgreja.papeisAtivos'])
            ->when($deveFiltrarBusca, function ($query) use ($busca): void {
                foreach (preg_split('/\s+/', $busca, -1, PREG_SPLIT_NO_EMPTY) ?: [] as $termo) {
                    $query->where(function ($subQuery) use ($termo): void {
                        $like = '%' . $termo . '%';
                        $cpfNumerico = preg_replace('/\D+/', '', $termo) ?? '';

                        $subQuery
                            ->whereRaw('LOWER(nome) LIKE ?', [$like])
                            ->orWhereRaw('LOWER(email) LIKE ?', [$like]);

                        if ($cpfNumerico !== '') {
                            $subQuery->orWhereRaw("REPLACE(REPLACE(REPLACE(REPLACE(cpf, '.', ''), '-', ''), '/', ''), ' ', '') LIKE ?", ['%' . $cpfNumerico . '%']);
                        }

                        $subQuery->orWhereHas('vinculosIgreja.igreja', function ($igrejaQuery) use ($like): void {
                            $igrejaQuery
                                ->whereRaw('LOWER(nome) LIKE ?', [$like])
                                ->orWhereRaw('LOWER(cidade) LIKE ?', [$like])
                                ->orWhereRaw('LOWER(estado) LIKE ?', [$like]);
                        });
                    });
                }
            })
            ->when($status !== '', fn ($query) => $query->where('ativo', $status === 'ativo'))
            ->when($tipo !== '', function ($query) use ($tipo): void {
                match ($tipo) {
                    'admin_master' => $query->where('perfil_global', 'admin_master'),
                    'padre' => $query->where('eh_padre', true),
                    'coordenador' => $query->whereHas('papeisAtivosPorIgreja', fn ($subQuery) => $subQuery->where('papel', PapelIgreja::COORDENADOR->value)),
                    'admin_local' => $query->whereHas('papeisAtivosPorIgreja', fn ($subQuery) => $subQuery->where('papel', PapelIgreja::ADMIN_LOCAL->value)),
                    'musico' => $query->whereHas('papeisAtivosPorIgreja', fn ($subQuery) => $subQuery->where('papel', PapelIgreja::MUSICO->value)),
                    'sem_vinculo' => $query
                        ->where('perfil_global', '!=', 'admin_master')
                        ->whereDoesntHave('vinculosIgrejaAtivos'),
                    default => null,
                };
            })
            ->when($presenca === 'online', function ($query) use ($usuariosOnlineIds): void {
                if ($usuariosOnlineIds->isEmpty()) {
                    $query->whereRaw('1 = 0');
                    return;
                }

                $query->whereIn('id', $usuariosOnlineIds->all());
            })
            ->when($presenca === 'offline' && $usuariosOnlineIds->isNotEmpty(), fn ($query) => $query->whereNotIn('id', $usuariosOnlineIds->all()))
            ->orderBy('nome')
            ->paginate(20)
            ->withQueryString();

        $usuarios->getCollection()->each(function (Usuario $usuario) use ($presencas, $limiteOnline): void {
            $ultimaAtividade = $presencas->get((int) $usuario->id);

            $usuario->setAttribute('presenca_online', is_int($ultimaAtividade) && $ultimaAtividade >= $limiteOnline);
            $usuario->setAttribute('ultima_atividade_em', is_int($ultimaAtividade)
                ? CarbonImmutable::createFromTimestamp($ultimaAtividade)
                : null);
        });

        return view('admin.users.index', [
            'usuarios' => $usuarios,
            'filtros' => [
                'q' => $buscaOriginal,
                'status' => $status,
                'tipo' => $tipo,
                'presenca' => $presenca,
                'busca_minima_atingida' => $buscaOriginal === '' || $deveFiltrarBusca,
            ],
            'metricas' => [
                'total' => Usuario::count(),
                'online' => $usuariosOnlineIds->count(),
                'admins_master' => Usuario::query()->where('perfil_global', 'admin_master')->count(),
                'padres' => Usuario::query()->where('eh_padre', true)->count(),
                'admins_locais' => UsuarioIgrejaPapel::query()->ativos()->doPapel(PapelIgreja::ADMIN_LOCAL)->count(),
                'coordenadores' => UsuarioIgrejaPapel::query()->ativos()->doPapel(PapelIgreja::COORDENADOR)->count(),
                'musicos' => UsuarioIgrejaPapel::query()->ativos()->doPapel(PapelIgreja::MUSICO)->count(),
                'sem_vinculo' => Usuario::query()
                    ->where('perfil_global', '!=', 'admin_master')
                    ->whereDoesntHave('vinculosIgrejaAtivos')
                    ->count(),
            ],
        ]);
    }

    private function obterPresencasUsuarios(): \Illuminate\Support\Collection
    {
        if (!Schema::hasTable('sessions')) {
            return collect();
        }

        return DB::table('sessions')
            ->select('user_id', DB::raw('MAX(last_activity) as last_activity'))
            ->whereNotNull('user_id')
            ->groupBy('user_id')
            ->pluck('last_activity', 'user_id')
            ->map(fn ($lastActivity): int => (int) $lastActivity);
    }

    public function create(): View
    {
        return view('admin.users.create', [
            'usuario' => new Usuario([
                'perfil_global' => 'usuario',
                'ativo' => true,
                'theme_preference' => 'system',
            ]),
            'igrejas' => Igreja::query()->orderBy('nome')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $dados = $this->validarCriacao($request);
        $ator = Auth::user();
        $tipoCadastro = (string) $dados['tipo_cadastro'];

        $usuario = $this->gestaoUsuariosIgrejaService->criarOuAtualizarContaBase(
            dados: [
                'nome' => $dados['nome'],
                'cpf' => $dados['cpf'],
                'email' => $dados['email'] ?? null,
                'telefone' => $dados['telefone'] ?? null,
                'ativo' => (bool) ($dados['ativo'] ?? true),
                'eh_padre' => $tipoCadastro === 'padre',
                'perfil_global' => $tipoCadastro === 'admin_master' ? 'admin_master' : 'usuario',
                'nivel_global' => $tipoCadastro === 'admin_master' ? 6 : 1,
            ],
            ator: $ator,
            origem: 'admin_usuarios_store'
        );

        $igreja = $this->resolverIgrejaOpcional($dados['igreja_id'] ?? null);
        $papelInicial = $this->papelInicialPorTipo($tipoCadastro);

        if ($igreja instanceof Igreja && $papelInicial instanceof PapelIgreja) {
            $this->gestaoUsuariosIgrejaService->atribuirPapeisAoUsuarioExistente(
                usuario: $usuario,
                igreja: $igreja,
                papeis: [$papelInicial],
                ator: $ator,
                origem: 'admin_usuarios_store_papel_inicial'
            );
        } elseif ($igreja instanceof Igreja && $tipoCadastro === 'padre') {
            $usuario->garantirVinculoNaIgreja($igreja);
        }

        return redirect()
            ->route('admin.usuarios.index')
            ->with('success', $this->mensagemCriacao($tipoCadastro, $igreja));
    }

    public function edit(Usuario $usuario): View|RedirectResponse
    {
        $this->autorizarGestaoDeContaMasterQuandoNecessario($usuario);

        /** @var Usuario|null $ator */
        $ator = Auth::user();

        if ($usuario->ehAdminMaster() && $ator?->id === $usuario->id) {
            return redirect()
                ->route('admin.profile')
                ->with('info', 'A conta admin master titular e gerenciada pela tela de Perfil.');
        }

        $usuario->load(['vinculosIgreja.igreja', 'vinculosIgreja.papeisAtivos']);

        return view('admin.users.edit', [
            'usuario' => $usuario,
            'igrejas' => Igreja::query()->orderBy('nome')->get(),
        ]);
    }

    public function update(Request $request, Usuario $usuario): RedirectResponse
    {
        $this->autorizarGestaoDeContaMasterQuandoNecessario($usuario);

        /** @var Usuario|null $ator */
        $ator = Auth::user();

        if ($usuario->ehAdminMaster() && $ator?->id === $usuario->id) {
            return redirect()
                ->route('admin.profile')
                ->with('info', 'Atualize sua propria conta admin master apenas pela tela de Perfil.');
        }

        $dados = $this->validarAtualizacao($request);

        if ($usuario->id === $ator?->id && (bool) ($dados['ativo'] ?? true) === false) {
            throw ValidationException::withMessages([
                'ativo' => 'Voce nao pode inativar a propria conta por este formulario.',
            ]);
        }

        $this->gestaoUsuariosIgrejaService->criarOuAtualizarContaBase(
            dados: [
                'nome' => $dados['nome'],
                'cpf' => $dados['cpf'],
                'email' => $dados['email'] ?? null,
                'telefone' => $dados['telefone'] ?? null,
                'ativo' => (bool) ($dados['ativo'] ?? true),
                'eh_padre' => (bool) ($dados['eh_padre'] ?? false),
                'perfil_global' => $dados['perfil_global'],
                'nivel_global' => $dados['perfil_global'] === 'admin_master' ? 6 : 1,
            ],
            ator: $ator,
            usuarioBase: $usuario,
            origem: 'admin_usuarios_update'
        );

        return redirect()
            ->route('admin.usuarios.index')
            ->with('success', 'Dados da conta atualizados com sucesso.');
    }

    public function storeVinculo(Request $request, Usuario $usuario): RedirectResponse
    {
        $dados = $request->validate([
            'igreja_id' => ['required', 'exists:igrejas,id'],
            'papeis' => ['nullable', 'array'],
            'papeis.*' => ['required', Rule::in(PapelIgreja::values())],
        ]);

        $igreja = Igreja::query()->findOrFail((int) $dados['igreja_id']);
        $papeisSelecionados = collect((array) ($dados['papeis'] ?? []))
            ->map(fn ($papel) => PapelIgreja::fromValue($papel))
            ->unique(fn (PapelIgreja $papel) => $papel->value)
            ->values();
        $papeisAtuais = $usuario->listarPapeisNaIgreja($igreja);

        $papeisParaConceder = $papeisSelecionados
            ->reject(fn (PapelIgreja $papel) => $papeisAtuais->contains(fn (PapelIgreja $atual) => $atual === $papel))
            ->values();

        $papeisParaRevogar = $papeisAtuais
            ->reject(fn (PapelIgreja $papel) => $papeisSelecionados->contains(fn (PapelIgreja $selecionado) => $selecionado === $papel))
            ->values();

        if ($papeisParaConceder->isNotEmpty()) {
            $this->gestaoUsuariosIgrejaService->atribuirPapeisAoUsuarioExistente(
                usuario: $usuario,
                igreja: $igreja,
                papeis: $papeisParaConceder->all(),
                ator: Auth::user(),
                origem: 'admin_usuarios_store_vinculo'
            );
        } else {
            $usuario->garantirVinculoNaIgreja($igreja);
        }

        foreach ($papeisParaRevogar as $papel) {
            $this->gestaoUsuariosIgrejaService->revogarPapelDeUsuarioExistente(
                usuario: $usuario,
                igreja: $igreja,
                papel: $papel,
                ator: Auth::user(),
                origem: 'admin_usuarios_store_vinculo'
            );
        }

        return redirect()
            ->route('admin.usuarios.index')
            ->with('success', 'Papeis da igreja atualizados com sucesso.');
    }

    public function toggle(Usuario $usuario): RedirectResponse
    {
        /** @var Usuario|null $ator */
        $ator = Auth::user();

        if ($usuario->ehAdminMaster()) {
            abort(403, 'A conta admin master nao pode ser ativada ou inativada por este fluxo.');
        }

        $usuario = $this->gestaoUsuariosIgrejaService->alterarStatusConta(
            usuario: $usuario,
            ativo: !$usuario->ativo,
            ator: $ator,
            contexto: [
                'origem' => 'admin_usuarios_toggle',
                'perfil_global' => $usuario->perfil_global,
            ]
        );

        return redirect()->route('admin.usuarios.index')->with('success', $usuario->ativo
            ? 'Conta reativada com sucesso.'
            : 'Conta inativada com sucesso.');
    }

    public function resetPassword(Request $request, Usuario $usuario): RedirectResponse
    {
        /** @var Usuario|null $ator */
        $ator = Auth::user();

        if ($usuario->ehAdminMaster()) {
            abort(403, 'A senha de admin master so pode ser alterada pelo proprio titular em Perfil.');
        }

        $this->gestaoUsuariosIgrejaService->enviarLinkDefinicaoSenha(
            usuario: $usuario,
            ator: $ator,
            contexto: [
                'origem' => 'admin_usuarios_reset_password',
                'perfil_global' => $usuario->perfil_global,
            ]
        );

        return back()->with('success', 'Link de definicao de senha enviado por e-mail. Ele expira em 60 minutos.');
    }

    public function hierarchy(): RedirectResponse
    {
        return redirect()->route('admin.usuarios.index');
    }

    private function validarCriacao(Request $request): array
    {
        $dados = $request->validate([
            'tipo_cadastro' => ['required', Rule::in(['admin_master', 'coordenador', 'admin_local', 'musico', 'padre'])],
            'igreja_id' => ['nullable', 'exists:igrejas,id'],
            'nome' => ['required', 'string', 'max:255'],
            'cpf' => ['required', 'string', 'max:14'],
            'email' => ['nullable', 'email', 'max:255'],
            'telefone' => ['nullable', 'string', 'max:20'],
            'ativo' => ['nullable', 'boolean'],
        ]);

        $this->garantirEmailQuandoNecessario(
            email: $dados['email'] ?? null,
            perfilGlobal: $dados['tipo_cadastro'] === 'admin_master' ? 'admin_master' : 'usuario',
            ehPadre: $dados['tipo_cadastro'] === 'padre'
        );

        if ($this->tipoCadastroExigeIgreja((string) $dados['tipo_cadastro']) && blank($dados['igreja_id'] ?? null)) {
            throw ValidationException::withMessages([
                'igreja_id' => 'Selecione uma igreja para cadastrar musico, admin local ou coordenador.',
            ]);
        }

        return $dados;
    }

    private function validarAtualizacao(Request $request): array
    {
        $dados = $request->validate([
            'perfil_global' => ['required', Rule::in(['admin_master', 'usuario'])],
            'nome' => ['required', 'string', 'max:255'],
            'cpf' => ['required', 'string', 'max:14'],
            'email' => ['nullable', 'email', 'max:255'],
            'telefone' => ['nullable', 'string', 'max:20'],
            'ativo' => ['nullable', 'boolean'],
            'eh_padre' => ['nullable', 'boolean'],
        ]);

        $this->garantirEmailQuandoNecessario(
            email: $dados['email'] ?? null,
            perfilGlobal: (string) $dados['perfil_global'],
            ehPadre: (bool) ($dados['eh_padre'] ?? false)
        );

        return $dados;
    }

    private function garantirEmailQuandoNecessario(?string $email, string $perfilGlobal, bool $ehPadre): void
    {
        if ($perfilGlobal === 'admin_master' && blank($email)) {
            throw ValidationException::withMessages([
                'email' => 'Admin master precisa de um e-mail valido para acesso.',
            ]);
        }

        if ($perfilGlobal !== 'admin_master' && !$ehPadre && blank($email)) {
            throw ValidationException::withMessages([
                'email' => 'Usuarios operacionais precisam de e-mail. Padre sem login pode ficar sem e-mail.',
            ]);
        }
    }

    private function papelInicialPorTipo(string $tipoCadastro): ?PapelIgreja
    {
        return match ($tipoCadastro) {
            'coordenador' => PapelIgreja::COORDENADOR,
            'admin_local' => PapelIgreja::ADMIN_LOCAL,
            'musico' => PapelIgreja::MUSICO,
            default => null,
        };
    }

    private function tipoCadastroExigeIgreja(string $tipoCadastro): bool
    {
        return in_array($tipoCadastro, ['coordenador', 'admin_local', 'musico'], true);
    }

    private function resolverIgrejaOpcional(mixed $igrejaId): ?Igreja
    {
        if (!is_numeric($igrejaId)) {
            return null;
        }

        return Igreja::query()->find((int) $igrejaId);
    }

    private function mensagemCriacao(string $tipoCadastro, ?Igreja $igreja): string
    {
        if ($tipoCadastro === 'admin_master') {
            return 'Admin master cadastrado com sucesso.';
        }

        if ($tipoCadastro === 'padre' && $igreja instanceof Igreja) {
            return 'Celebrante cadastrado e vinculado com sucesso.';
        }

        if ($tipoCadastro === 'padre') {
            return 'Celebrante cadastrado com sucesso.';
        }

        if (!($igreja instanceof Igreja)) {
            return 'Conta criada com sucesso. Para liberar papeis operacionais, abra o usuario na lista e vincule uma igreja.';
        }

        return 'Conta criada e vinculada com sucesso.';
    }

    private function normalizarBusca(string $busca): string
    {
        return mb_strtolower(trim(preg_replace('/\s+/', ' ', $busca) ?? ''));
    }

    private function autorizarGestaoDeContaMasterQuandoNecessario(Usuario $usuario): void
    {
        /** @var Usuario|null $ator */
        $ator = Auth::user();

        if (!$usuario->ehAdminMaster()) {
            return;
        }

        if ($ator?->id === $usuario->id) {
            return;
        }

        abort(403, 'Admins master so podem gerenciar a propria conta por este fluxo.');
    }
}
