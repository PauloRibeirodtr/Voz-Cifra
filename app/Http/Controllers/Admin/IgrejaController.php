<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PapelIgreja;
use App\Http\Controllers\Controller;
use App\Models\Igreja;
use App\Models\Usuario;
use App\Models\UsuarioIgreja;
use App\Models\UsuarioIgrejaPapel;
use App\Rules\StrongPassword;
use App\Services\NotificacaoSegurancaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class IgrejaController extends Controller
{
    public function __construct(
        private readonly NotificacaoSegurancaService $notificacaoSegurancaService
    ) {
    }

    public function index(): View
    {
        $igrejas = Igreja::with([
            'adminsLocais' => fn ($query) => $query
                ->orderBy('nome'),
        ])
            ->orderBy('nome')
            ->get()
            ->map(fn (Igreja $igreja) => $this->adicionarDadosPublicos($igreja));

        return view('admin.churches.index', [
            'igrejas' => $igrejas,
        ]);
    }

    public function create(): View
    {
        return view('admin.churches.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $dados = $request->validate([
            'nome' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'alpha_dash', Rule::unique('igrejas', 'slug')],
            'cnpj' => ['required', 'string', 'max:18', Rule::unique('igrejas', 'cnpj')],
            'cep' => ['nullable', 'string', 'max:9'],
            'endereco' => ['nullable', 'string', 'max:255'],
            'cidade' => ['required', 'string', 'max:255'],
            'estado' => ['required', 'string', 'size:2'],
            'ativo' => ['nullable', 'boolean'],
            'admin_nome' => ['nullable', 'string', 'max:255'],
            'admin_cpf' => ['nullable', 'string', 'max:14', Rule::unique('usuarios', 'cpf')],
            'admin_email' => ['nullable', 'email', 'max:255', Rule::unique('usuarios', 'email')],
            'admin_telefone' => ['nullable', 'string', 'max:20'],
        ]);

        $this->validarConjuntoAdminLocal($request);

        /** @var \App\Models\Usuario|null $ator */
        $ator = Auth::user();
        $adminLocalCriado = null;

        $igreja = DB::transaction(function () use ($dados, &$adminLocalCriado): Igreja {
            $slug = $this->resolverSlug(
                $dados['slug'] ?? null,
                $dados['nome']
            );

            $igreja = Igreja::create([
                'nome' => $dados['nome'],
                'slug' => $slug,
                'cnpj' => $dados['cnpj'],
                'cep' => $dados['cep'] ?? null,
                'endereco' => $dados['endereco'] ?? null,
                'cidade' => $dados['cidade'],
                'estado' => strtoupper($dados['estado']),
                'ativo' => (bool) ($dados['ativo'] ?? true),
            ]);

            if ($this->dadosDeAdminLocalPreenchidos($dados)) {
                $adminLocalCriado = $this->criarAdminLocalDaIgreja($igreja, $dados);
            }

            return $igreja;
        });

        if ($adminLocalCriado instanceof Usuario) {
            $this->notificacaoSegurancaService->enviarEventoConta(
                alvo: $adminLocalCriado,
                evento: 'papel_local_concedido',
                ator: $ator,
                contexto: [
                    'origem' => 'admin_igrejas_store',
                    'igreja_id' => $igreja->id,
                    'papel' => 'admin_local',
                ]
            );
        }

        return redirect()
            ->route('admin.igrejas.edit', $igreja)
            ->with('success', $adminLocalCriado instanceof Usuario
                ? 'Igreja e administrador local cadastrados com sucesso. O link publico fixo e o QR Code desta igreja ja estao prontos para uso futuro.'
                : 'Igreja cadastrada com sucesso. Voce pode vincular o admin local depois, quando a unidade estiver pronta.');
    }

    public function edit(Igreja $igreja): View
    {
        $adminsLocais = $this->obterAdminsLocais($igreja);
        $igreja = $this->adicionarDadosPublicos($igreja);

        return view('admin.churches.edit', [
            'igreja' => $igreja,
            'adminLocal' => $adminsLocais->first(),
            'adminsLocais' => $adminsLocais,
        ]);
    }

    public function update(Request $request, Igreja $igreja): RedirectResponse
    {
        $adminLocal = $this->obterAdminLocal($igreja);

        $dados = $request->validate([
            'nome' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'alpha_dash', Rule::unique('igrejas', 'slug')->ignore($igreja->id)],
            'cnpj' => ['required', 'string', 'max:18', Rule::unique('igrejas', 'cnpj')->ignore($igreja->id)],
            'cep' => ['nullable', 'string', 'max:9'],
            'endereco' => ['nullable', 'string', 'max:255'],
            'cidade' => ['required', 'string', 'max:255'],
            'estado' => ['required', 'string', 'size:2'],
            'ativo' => ['nullable', 'boolean'],
            'admin_nome' => ['nullable', 'string', 'max:255'],
            'admin_cpf' => ['nullable', 'string', 'max:14', Rule::unique('usuarios', 'cpf')->ignore($adminLocal?->id)],
            'admin_email' => ['nullable', 'email', 'max:255', Rule::unique('usuarios', 'email')->ignore($adminLocal?->id)],
            'admin_telefone' => ['nullable', 'string', 'max:20'],
        ]);

        $this->validarConjuntoAdminLocal($request);

        DB::transaction(function () use ($dados, $igreja, $adminLocal): void {
            $slug = $this->resolverSlug(
                $dados['slug'] ?? null,
                $dados['nome'],
                $igreja->id
            );

            $igreja->update([
                'nome' => $dados['nome'],
                'slug' => $slug,
                'cnpj' => $dados['cnpj'],
                'cep' => $dados['cep'] ?? null,
                'endereco' => $dados['endereco'] ?? null,
                'cidade' => $dados['cidade'],
                'estado' => strtoupper($dados['estado']),
                'ativo' => (bool) ($dados['ativo'] ?? false),
            ]);

            if (!$this->dadosDeAdminLocalPreenchidos($dados)) {
                return;
            }

            if (!$adminLocal) {
                $this->criarAdminLocalDaIgreja($igreja, $dados);

                return;
            }

            $dadosAdmin = [
                'igreja_id' => $igreja->id,
                'nome' => $dados['admin_nome'],
                'cpf' => $dados['admin_cpf'],
                'email' => $dados['admin_email'],
                'telefone' => $dados['admin_telefone'] ?? null,
                'perfil_global' => 'usuario',
                'nivel_global' => 5,
                'ativo' => true,
            ];

            $adminLocal->update($dadosAdmin);
            $this->sincronizarVinculoAdminLocal($adminLocal, $igreja);
        });

        return redirect()
            ->route('admin.igrejas.edit', $igreja)
            ->with('success', 'Igreja atualizada com sucesso.');
    }

    public function resetAdminLocalPassword(Request $request, Igreja $igreja): RedirectResponse
    {
        $adminLocal = $request->filled('admin_local_id')
            ? $this->obterAdminLocalPorId($igreja, (int) $request->input('admin_local_id'))
            : $this->obterAdminLocal($igreja);
        $origem = $request->input('origem') === 'edit' ? 'edit' : 'index';

        if (!$adminLocal) {
            return redirect()
                ->route($origem === 'edit' ? 'admin.igrejas.edit' : 'admin.igrejas.index', $origem === 'edit' ? $igreja : [])
                ->with('error', 'Esta igreja ainda nao possui administrador local cadastrado.');
        }

        $validator = Validator::make($request->all(), [
            'password' => ['nullable', 'confirmed', new StrongPassword()],
        ], [
            'password.confirmed' => 'A confirmacao da nova senha nao confere.',
        ]);

        if ($validator->fails()) {
            $redirecionamento = redirect()
                ->route($origem === 'edit' ? 'admin.igrejas.edit' : 'admin.igrejas.index', $origem === 'edit' ? $igreja : [])
                ->withErrors($validator)
                ->withInput();

            if ($origem === 'index') {
                $redirecionamento->with('abrir_reset_modal', 'resetar-admin-local-' . $igreja->id . '-' . $adminLocal?->id);
            }

            return $redirecionamento;
        }

        $dados = $validator->validated();

        $adminLocal->update([
            'password' => filled($dados['password'] ?? null)
                ? $dados['password']
                : $this->gerarSenhaInicialPorCpf($adminLocal->cpf),
            'primeiro_acesso' => true,
        ]);

        /** @var \App\Models\Usuario|null $ator */
        $ator = Auth::user();
        $this->notificacaoSegurancaService->enviarEventoConta(
            alvo: $adminLocal,
            evento: 'reset_senha',
            ator: $ator,
            contexto: [
                'origem' => 'admin_igrejas_reset_admin_local',
                'igreja_id' => $igreja->id,
                'senha_inicial' => filled($dados['password'] ?? null) ? 'definida_manual' : 'cpf_sem_pontuacao',
            ]
        );

        return redirect()
            ->route($origem === 'edit' ? 'admin.igrejas.edit' : 'admin.igrejas.index', $origem === 'edit' ? $igreja : [])
            ->with('success', 'Senha redefinida com sucesso. O usuario devera trocar no proximo acesso.');
    }

    public function storeAdminLocal(Request $request, Igreja $igreja): RedirectResponse
    {
        if ($this->obterAdminsLocais($igreja)->count() >= 2) {
            return redirect()
                ->route('admin.igrejas.edit', $igreja)
                ->with('error', 'Cada igreja pode ter no maximo 2 admins locais.');
        }

        $dados = $request->validate([
            'nome' => ['required', 'string', 'max:255'],
            'cpf' => ['required', 'string', 'max:14', Rule::unique('usuarios', 'cpf')],
            'email' => ['required', 'email', 'max:255', Rule::unique('usuarios', 'email')],
            'telefone' => ['nullable', 'string', 'max:20'],
        ]);

        $adminLocal = $this->criarAdminLocalDaIgreja($igreja, [
            'admin_nome' => $dados['nome'],
            'admin_cpf' => $dados['cpf'],
            'admin_email' => $dados['email'],
            'admin_telefone' => $dados['telefone'] ?? null,
        ]);

        /** @var \App\Models\Usuario|null $ator */
        $ator = Auth::user();
        $this->notificacaoSegurancaService->enviarEventoConta(
            alvo: $adminLocal,
            evento: 'papel_local_concedido',
            ator: $ator,
            contexto: [
                'origem' => 'admin_igrejas_store_admin_local',
                'igreja_id' => $igreja->id,
                'papel' => 'admin_local',
            ]
        );

        return redirect()
            ->route('admin.igrejas.edit', $igreja)
            ->with('success', 'Admin local adicional cadastrado com sucesso.');
    }

    protected function obterAdminLocal(Igreja $igreja): ?Usuario
    {
        return $this->obterAdminsLocais($igreja)->first();
    }

    protected function obterAdminsLocais(Igreja $igreja)
    {
        $adminsLocais = $igreja->usuariosComPapelAdminLocal()
            ->orderBy('id')
            ->get();

        if ($adminsLocais->isNotEmpty()) {
            return $adminsLocais;
        }

        return $igreja->usuarios()
            ->where('perfil_global', 'admin_local')
            ->orderBy('id')
            ->get();
    }

    protected function obterAdminLocalPorId(Igreja $igreja, int $adminLocalId): ?Usuario
    {
        return $this->obterAdminsLocais($igreja)
            ->firstWhere('id', $adminLocalId);
    }

    protected function resolverSlug(?string $slugInformado, string $nome, ?int $ignorarIgrejaId = null): string
    {
        $base = Str::slug($slugInformado ?: $nome);

        if ($base === '') {
            $base = 'igreja';
        }

        $slug = $base;
        $contador = 2;

        while ($this->slugJaExiste($slug, $ignorarIgrejaId)) {
            $slug = "{$base}-{$contador}";
            $contador++;
        }

        return $slug;
    }

    protected function slugJaExiste(string $slug, ?int $ignorarIgrejaId = null): bool
    {
        return Igreja::query()
            ->when($ignorarIgrejaId, fn ($query) => $query->where('id', '!=', $ignorarIgrejaId))
            ->where('slug', $slug)
            ->exists();
    }

    protected function gerarSenhaInicialPorCpf(string $cpf): string
    {
        $cpfNumerico = $this->somenteDigitos($cpf);

        if (strlen($cpfNumerico) < 8) {
            throw ValidationException::withMessages([
                'admin_cpf' => 'Informe um CPF valido para gerar a senha inicial.',
            ]);
        }

        return $cpfNumerico;
    }

    protected function somenteDigitos(string $valor): string
    {
        return preg_replace('/\D+/', '', $valor) ?? '';
    }

    protected function adicionarDadosPublicos(Igreja $igreja): Igreja
    {
        $linkPublico = route('igrejas.public.show', ['slug' => $igreja->slug]);

        $igreja->setAttribute('link_publico', $linkPublico);
        $igreja->setAttribute(
            'qr_code_url',
            'https://api.qrserver.com/v1/create-qr-code/?size=260x260&data=' . urlencode($linkPublico)
        );

        return $igreja;
    }

    protected function criarAdminLocalDaIgreja(Igreja $igreja, array $dados): Usuario
    {
        $adminLocal = Usuario::create([
            'igreja_id' => $igreja->id,
            'nome' => $dados['admin_nome'],
            'cpf' => $dados['admin_cpf'],
            'email' => $dados['admin_email'],
            'telefone' => $dados['admin_telefone'] ?? null,
            'password' => $this->gerarSenhaInicialPorCpf($dados['admin_cpf']),
            'perfil_global' => 'usuario',
            'nivel_global' => 5,
            'ativo' => true,
            'primeiro_acesso' => true,
        ]);

        $this->sincronizarVinculoAdminLocal($adminLocal, $igreja);

        return $adminLocal;
    }

    protected function sincronizarVinculoAdminLocal(Usuario $usuario, Igreja $igreja): void
    {
        $vinculo = UsuarioIgreja::updateOrCreate(
            [
                'usuario_id' => $usuario->id,
                'igreja_id' => $igreja->id,
            ],
            [
                'ativo' => true,
                'desvinculado_em' => null,
                'responsavel_principal' => false,
                'vinculado_em' => now(),
            ]
        );

        UsuarioIgrejaPapel::updateOrCreate(
            [
                'usuario_igreja_id' => $vinculo->id,
                'papel' => PapelIgreja::ADMIN_LOCAL->value,
            ],
            [
                'ativo' => true,
                'concedido_em' => now(),
            ]
        );
    }

    protected function dadosDeAdminLocalPreenchidos(array $dados): bool
    {
        return filled($dados['admin_nome'] ?? null)
            || filled($dados['admin_cpf'] ?? null)
            || filled($dados['admin_email'] ?? null)
            || filled($dados['admin_telefone'] ?? null);
    }

    protected function validarConjuntoAdminLocal(Request $request): void
    {
        $campos = [
            'admin_nome',
            'admin_cpf',
            'admin_email',
            'admin_telefone',
        ];

        $preenchidos = collect($campos)->contains(fn (string $campo) => filled($request->input($campo)));

        if (!$preenchidos) {
            return;
        }

        Validator::make($request->all(), [
            'admin_nome' => ['required', 'string', 'max:255'],
            'admin_cpf' => ['required', 'string', 'max:14'],
            'admin_email' => ['required', 'email', 'max:255'],
        ], [
            'admin_nome.required' => 'Informe o nome do admin local ou deixe o bloco em branco para cadastrar depois.',
            'admin_cpf.required' => 'Informe o CPF do admin local ou deixe o bloco em branco para cadastrar depois.',
            'admin_email.required' => 'Informe o e-mail do admin local ou deixe o bloco em branco para cadastrar depois.',
        ])->validate();
    }
}
