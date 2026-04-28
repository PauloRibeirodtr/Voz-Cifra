<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PapelIgreja;
use App\Http\Controllers\Controller;
use App\Models\Igreja;
use App\Models\Usuario;
use App\Models\UsuarioIgrejaPapel;
use App\Rules\StrongPassword;
use App\Services\GestaoUsuariosIgrejaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        $busca = trim((string) $request->string('q'));
        $status = trim((string) $request->string('status'));
        $tipo = trim((string) $request->string('tipo'));

        $usuarios = Usuario::query()
            ->with(['vinculosIgreja.igreja', 'vinculosIgreja.papeisAtivos'])
            ->when($busca !== '', function ($query) use ($busca): void {
                $query->where(function ($subQuery) use ($busca): void {
                    $subQuery->where('nome', 'like', '%' . $busca . '%')
                        ->orWhere('email', 'like', '%' . $busca . '%')
                        ->orWhere('cpf', 'like', '%' . $busca . '%');
                });
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
            ->orderBy('nome')
            ->paginate(20)
            ->withQueryString();

        return view('admin.users.index', [
            'usuarios' => $usuarios,
            'filtros' => [
                'q' => $busca,
                'status' => $status,
                'tipo' => $tipo,
            ],
            'metricas' => [
                'total' => Usuario::count(),
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
                'password' => $dados['password'] ?? null,
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
                'password' => $dados['password'] ?? null,
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

        $dados = $request->validate([
            'password' => ['nullable', 'confirmed', new StrongPassword()],
        ]);

        $this->gestaoUsuariosIgrejaService->redefinirSenhaProvisoria(
            usuario: $usuario,
            senha: $dados['password'] ?? null,
            ator: $ator,
            contexto: [
                'origem' => 'admin_usuarios_reset_password',
                'perfil_global' => $usuario->perfil_global,
            ]
        );

        return back()->with('success', 'Senha redefinida com sucesso. O usuario devera trocar no proximo acesso.');
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
            'password' => ['nullable', 'confirmed', new StrongPassword()],
            'ativo' => ['nullable', 'boolean'],
        ]);

        $this->garantirEmailQuandoNecessario(
            email: $dados['email'] ?? null,
            perfilGlobal: $dados['tipo_cadastro'] === 'admin_master' ? 'admin_master' : 'usuario',
            ehPadre: $dados['tipo_cadastro'] === 'padre'
        );

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
            'password' => ['nullable', 'confirmed', new StrongPassword()],
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
