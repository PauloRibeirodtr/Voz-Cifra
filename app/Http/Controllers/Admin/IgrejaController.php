<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PapelIgreja;
use App\Http\Controllers\Controller;
use App\Models\Igreja;
use App\Models\Usuario;
use App\Services\AuditoriaOperacionalService;
use App\Services\GestaoUsuariosIgrejaService;
use App\Services\StatusOperacionalIgrejaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class IgrejaController extends Controller
{
    public function __construct(
        private readonly GestaoUsuariosIgrejaService $gestaoUsuariosIgrejaService,
        private readonly StatusOperacionalIgrejaService $statusOperacionalIgrejaService,
        private readonly AuditoriaOperacionalService $auditoriaOperacionalService
    ) {
    }

    public function index(Request $request): View
    {
        $busca = trim((string) $request->input('busca', ''));
        $filtroStatus = (string) $request->input('status', 'todas');

        if (!in_array($filtroStatus, ['todas', 'operacionais', 'aguardando'], true)) {
            $filtroStatus = 'todas';
        }

        $igrejasBase = Igreja::with([
            'adminsLocais' => fn ($query) => $query->orderBy('nome'),
            'coordenadores' => fn ($query) => $query->orderBy('nome'),
        ])
            ->orderBy('nome')
            ->get()
            ->map(fn (Igreja $igreja) => $this->adicionarDadosPublicos($igreja));

        $sugestoesIgrejas = $igrejasBase
            ->map(fn (Igreja $igreja) => [
                'nome' => $igreja->nome,
                'cidade' => $igreja->cidade,
                'estado' => $igreja->estado,
            ])
            ->values();

        if ($busca !== '') {
            $igrejasBase = $igrejasBase
                ->filter(fn (Igreja $igreja) => $this->igrejaCombinaComBusca($igreja, $busca))
                ->values();
        }

        $totalIgrejas = $igrejasBase->count();
        $igrejasOperacionais = $igrejasBase->filter(fn (Igreja $igreja) => $igreja->estaOperacional())->count();
        $igrejasAguardando = $totalIgrejas - $igrejasOperacionais;

        $igrejas = $igrejasBase
            ->filter(function (Igreja $igreja) use ($filtroStatus): bool {
                return match ($filtroStatus) {
                    'operacionais' => $igreja->estaOperacional(),
                    'aguardando' => !$igreja->estaOperacional(),
                    default => true,
                };
            })
            ->values();

        return view('admin.churches.index', [
            'igrejas' => $igrejas,
            'busca' => $busca,
            'filtroStatus' => $filtroStatus,
            'totalIgrejas' => $totalIgrejas,
            'igrejasOperacionais' => $igrejasOperacionais,
            'igrejasAguardando' => $igrejasAguardando,
            'sugestoesIgrejas' => $sugestoesIgrejas,
        ]);
    }

    public function create(): View
    {
        return view('admin.churches.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->merge([
            'criar_admin_local_agora' => $this->normalizarOpcaoAdminLocal($request->input('criar_admin_local_agora')),
        ]);

        $dados = $request->validate([
            'nome' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'alpha_dash', Rule::unique('igrejas', 'slug')],
            'cnpj' => ['required', 'string', 'max:18'],
            'telefone_secretaria' => ['nullable', 'string', 'max:20'],
            'cep' => ['nullable', 'string', 'max:9'],
            'endereco' => ['nullable', 'string', 'max:255'],
            'cidade' => ['required', 'string', 'max:255'],
            'estado' => ['required', 'string', 'size:2'],
            'imagem' => ['nullable', 'image', 'max:2048'],
            'ativo' => ['nullable', 'boolean'],
            'criar_admin_local_agora' => ['nullable', 'boolean'],
            'admin_nome' => ['nullable', 'string', 'max:255'],
            'admin_cpf' => ['nullable', 'string', 'max:14'],
            'admin_email' => ['nullable', 'email', 'max:255'],
            'admin_telefone' => ['nullable', 'string', 'max:20'],
            'confirmar_duplicidade' => ['nullable', 'boolean'],
        ]);

        $this->validarConjuntoAdminLocal($request);
        $duplicidades = $this->detectarIgrejasParecidas($dados);

        if ($duplicidades->isNotEmpty() && !((bool) ($dados['confirmar_duplicidade'] ?? false))) {
            return back()
                ->withInput()
                ->with('duplicidade_igreja', [
                    'mensagem' => 'Ja existe uma igreja parecida cadastrada. Deseja continuar mesmo assim?',
                    'igrejas' => $duplicidades->all(),
                ]);
        }

        /** @var \App\Models\Usuario|null $ator */
        $ator = Auth::user();
        $adminLocalCriado = null;

        $igreja = DB::transaction(function () use ($dados, $ator, &$adminLocalCriado): Igreja {
            $slug = $this->resolverSlug(
                $dados['slug'] ?? null,
                $dados['nome']
            );

            $igreja = Igreja::create([
                'nome' => $dados['nome'],
                'slug' => $slug,
                'cnpj' => $dados['cnpj'],
                'telefone_secretaria' => $dados['telefone_secretaria'] ?? null,
                'cep' => $dados['cep'] ?? null,
                'endereco' => $dados['endereco'] ?? null,
                'cidade' => $dados['cidade'],
                'estado' => strtoupper($dados['estado']),
                'status_operacional' => 'aguardando_admin_local',
                'ativo' => (bool) ($dados['ativo'] ?? true),
            ]);

            if ($this->dadosDeAdminLocalPreenchidos($dados)) {
                $adminLocalCriado = $this->criarAdminLocalDaIgreja($igreja, $dados, $ator, 'admin_igrejas_store');
            }

            return $igreja;
        });

        if ($request->hasFile('imagem')) {
            $igreja->imagem_path = $request->file('imagem')->store('igrejas/imagens', $this->discoUploadsPublicos());
            $igreja->save();
        }

        $igreja = $this->statusOperacionalIgrejaService->atualizar($igreja);
        $this->auditoriaOperacionalService->registrar(
            evento: 'igreja_criada',
            ator: $ator,
            alvo: $adminLocalCriado,
            igreja: $igreja,
            contexto: [
                'origem' => 'admin_igrejas_store',
                'status_operacional' => $igreja->status_operacional,
                'admin_local_vinculado' => $adminLocalCriado instanceof Usuario,
                'resumo' => $adminLocalCriado instanceof Usuario
                    ? 'Igreja criada e liberada para operacao com admin local ativo.'
                    : 'Igreja criada sem admin local e mantida em aguardando admin local.',
            ]
        );

        return redirect()
            ->route('admin.igrejas.edit', $igreja)
            ->with('success', $adminLocalCriado instanceof Usuario
                ? 'Igreja cadastrada com sucesso. O administrador local foi vinculado e a unidade ja esta operacional para missas, repertorios e publicacoes.'
                : 'Igreja cadastrada com sucesso. A unidade ficou em aguardando admin local e ainda nao pode operar missas, repertorios ou publicacoes.');
    }

    public function edit(Igreja $igreja): View
    {
        $adminsLocais = $this->obterAdminsLocais($igreja);
        $coordenadores = $this->obterCoordenadores($igreja);
        $usuariosVinculados = $igreja->vinculosUsuarios()
            ->where('ativo', true)
            ->with(['usuario', 'papeisAtivos'])
            ->get()
            ->filter(fn ($vinculo) => $vinculo->usuario !== null)
            ->values();
        $igreja = $this->adicionarDadosPublicos($igreja);

        return view('admin.churches.edit', [
            'igreja' => $igreja,
            'adminLocal' => $adminsLocais->first(),
            'adminsLocais' => $adminsLocais,
            'coordenadores' => $coordenadores,
            'usuariosVinculados' => $usuariosVinculados,
        ]);
    }

    public function update(Request $request, Igreja $igreja): RedirectResponse
    {
        $request->merge([
            'criar_admin_local_agora' => $this->normalizarOpcaoAdminLocal($request->input('criar_admin_local_agora')),
        ]);

        $adminLocal = $this->obterAdminLocal($igreja);

        $dados = $request->validate([
            'nome' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'alpha_dash', Rule::unique('igrejas', 'slug')->ignore($igreja->id)],
            'cnpj' => ['required', 'string', 'max:18'],
            'telefone_secretaria' => ['nullable', 'string', 'max:20'],
            'cep' => ['nullable', 'string', 'max:9'],
            'endereco' => ['nullable', 'string', 'max:255'],
            'cidade' => ['required', 'string', 'max:255'],
            'estado' => ['required', 'string', 'size:2'],
            'imagem' => ['nullable', 'image', 'max:2048'],
            'ativo' => ['nullable', 'boolean'],
            'criar_admin_local_agora' => ['nullable', 'boolean'],
            'admin_nome' => ['nullable', 'string', 'max:255'],
            'admin_cpf' => ['nullable', 'string', 'max:14'],
            'admin_email' => ['nullable', 'email', 'max:255'],
            'admin_telefone' => ['nullable', 'string', 'max:20'],
        ]);

        $this->validarConjuntoAdminLocal($request);

        /** @var \App\Models\Usuario|null $ator */
        $ator = Auth::user();

        DB::transaction(function () use ($dados, $igreja, $adminLocal, $ator): void {
            $slug = $this->resolverSlug(
                $dados['slug'] ?? null,
                $dados['nome'],
                $igreja->id
            );

            $igreja->update([
                'nome' => $dados['nome'],
                'slug' => $slug,
                'cnpj' => $dados['cnpj'],
                'telefone_secretaria' => $dados['telefone_secretaria'] ?? null,
                'cep' => $dados['cep'] ?? null,
                'endereco' => $dados['endereco'] ?? null,
                'cidade' => $dados['cidade'],
                'estado' => strtoupper($dados['estado']),
                'ativo' => (bool) ($dados['ativo'] ?? false),
            ]);

            if (!$this->dadosDeAdminLocalPreenchidos($dados)) {
                return;
            }

            $this->criarAdminLocalDaIgreja(
                igreja: $igreja,
                dados: $dados,
                ator: $ator,
                origem: 'admin_igrejas_update',
                usuarioBase: $adminLocal
            );
        });

        if ($request->hasFile('imagem')) {
            $caminhoAnterior = $igreja->imagem_path;
            $igreja->imagem_path = $request->file('imagem')->store('igrejas/imagens', $this->discoUploadsPublicos());
            $igreja->save();

            if (is_string($caminhoAnterior) && $caminhoAnterior !== '') {
                Storage::disk($this->discoUploadsPublicos())->delete($caminhoAnterior);
            }
        }

        $igreja = $this->statusOperacionalIgrejaService->atualizar($igreja);
        $this->auditoriaOperacionalService->registrar(
            evento: 'igreja_editada',
            ator: $ator,
            alvo: $this->obterAdminLocal($igreja),
            igreja: $igreja,
            contexto: [
                'origem' => 'admin_igrejas_update',
                'status_operacional' => $igreja->status_operacional,
                'admin_local_revisado' => $this->dadosDeAdminLocalPreenchidos($dados),
                'resumo' => $igreja->estaOperacional()
                    ? 'Dados da igreja atualizados mantendo a unidade operacional.'
                    : 'Dados da igreja atualizados; a unidade segue aguardando admin local.',
            ]
        );

        return redirect()
            ->route('admin.igrejas.index')
            ->with('success', $igreja->estaOperacional()
                ? 'Igreja atualizada com sucesso. A unidade segue operacional.'
                : 'Igreja atualizada com sucesso. A unidade continua aguardando admin local para operar missas, repertorios e publicacoes.');
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

        $this->gestaoUsuariosIgrejaService->enviarLinkDefinicaoSenha(
            usuario: $adminLocal,
            ator: Auth::user(),
            contexto: [
                'origem' => 'admin_igrejas_reset_admin_local',
                'igreja_id' => $igreja->id,
                'igreja_nome' => $igreja->nome,
            ]
        );

        return redirect()
            ->route($origem === 'edit' ? 'admin.igrejas.edit' : 'admin.igrejas.index', $origem === 'edit' ? $igreja : [])
            ->with('success', 'Link de definicao de senha enviado por e-mail. Ele expira em 60 minutos.');
    }

    public function storeAdminLocal(Request $request, Igreja $igreja): RedirectResponse
    {
        $dados = $request->validate([
            'nome' => ['required', 'string', 'max:255'],
            'cpf' => ['required', 'string', 'max:14'],
            'email' => ['required', 'email', 'max:255'],
            'telefone' => ['nullable', 'string', 'max:20'],
        ]);

        $adminLocal = $this->criarAdminLocalDaIgreja($igreja, [
            'admin_nome' => $dados['nome'],
            'admin_cpf' => $dados['cpf'],
            'admin_email' => $dados['email'],
            'admin_telefone' => $dados['telefone'] ?? null,
        ], Auth::user(), 'admin_igrejas_store_admin_local');

        $this->statusOperacionalIgrejaService->atualizar($igreja);

        return redirect()
            ->route('admin.igrejas.edit', $igreja)
            ->with('success', 'Administrador local vinculado com sucesso. A unidade foi atualizada para o fluxo operacional correto.');
    }

    public function storeCoordenador(Request $request, Igreja $igreja): RedirectResponse
    {
        $dados = $request->validate([
            'nome' => ['required', 'string', 'max:255'],
            'cpf' => ['required', 'string', 'max:14'],
            'email' => ['required', 'email', 'max:255'],
            'telefone' => ['nullable', 'string', 'max:20'],
        ]);

        $this->gestaoUsuariosIgrejaService->criarOuAtualizarContaOperacional(
            dados: [
                'nome' => $dados['nome'],
                'cpf' => $dados['cpf'],
                'email' => $dados['email'],
                'telefone' => $dados['telefone'] ?? null,
                'ativo' => true,
            ],
            igreja: $igreja,
            papeis: [PapelIgreja::COORDENADOR],
            ator: Auth::user(),
            origem: 'admin_igrejas_store_coordenador'
        );

        return redirect()
            ->route('admin.igrejas.edit', $igreja)
            ->with('success', 'Coordenador vinculado a igreja com sucesso.');
    }

    public function storePapelUsuarioVinculado(Request $request, Igreja $igreja, Usuario $usuario): RedirectResponse
    {
        $dados = $request->validate([
            'papel' => ['required', Rule::in([
                PapelIgreja::ADMIN_LOCAL->value,
                PapelIgreja::COORDENADOR->value,
                PapelIgreja::MUSICO->value,
            ])],
        ]);

        abort_unless($usuario->vinculoNaIgreja($igreja)?->ativo, 404);

        $papel = PapelIgreja::fromValue((string) $dados['papel']);

        $this->gestaoUsuariosIgrejaService->atribuirPapeisAoUsuarioExistente(
            usuario: $usuario,
            igreja: $igreja,
            papeis: [$papel],
            ator: Auth::user(),
            origem: 'admin_igrejas_store_papel_usuario_vinculado'
        );

        return redirect()
            ->route('admin.igrejas.edit', $igreja)
            ->with('success', sprintf('%s agora atua como %s nesta igreja.', $usuario->nome, mb_strtolower($papel->label())));
    }

    public function destroyPapelUsuarioVinculado(Request $request, Igreja $igreja, Usuario $usuario): RedirectResponse
    {
        $dados = $request->validate([
            'papel' => ['required', Rule::in([
                PapelIgreja::ADMIN_LOCAL->value,
                PapelIgreja::COORDENADOR->value,
                PapelIgreja::MUSICO->value,
            ])],
        ]);

        abort_unless($usuario->vinculoNaIgreja($igreja)?->ativo, 404);

        $papel = PapelIgreja::fromValue((string) $dados['papel']);

        $this->gestaoUsuariosIgrejaService->revogarPapelDeUsuarioExistente(
            usuario: $usuario,
            igreja: $igreja,
            papel: $papel,
            ator: Auth::user(),
            origem: 'admin_igrejas_destroy_papel_usuario_vinculado'
        );

        return redirect()
            ->route('admin.igrejas.edit', $igreja)
            ->with('success', sprintf('%s deixou de atuar como %s nesta igreja.', $usuario->nome, mb_strtolower($papel->label())));
    }

    protected function obterAdminLocal(Igreja $igreja): ?Usuario
    {
        return $this->obterAdminsLocais($igreja)->first();
    }

    protected function obterAdminsLocais(Igreja $igreja)
    {
        return $igreja->usuariosComPapel(PapelIgreja::ADMIN_LOCAL)
            ->orderBy('nome')
            ->get();
    }

    protected function obterAdminLocalPorId(Igreja $igreja, int $adminLocalId): ?Usuario
    {
        return $this->obterAdminsLocais($igreja)
            ->firstWhere('id', $adminLocalId);
    }

    protected function obterCoordenadores(Igreja $igreja)
    {
        return $igreja->usuariosComPapel(PapelIgreja::COORDENADOR)
            ->orderBy('nome')
            ->get();
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

    protected function adicionarDadosPublicos(Igreja $igreja): Igreja
    {
        $linkPublico = route('igrejas.public.show', ['slug' => $igreja->slug]);
        $linkPublicoMusicos = route('igrejas.public.musicos.show', ['slug' => $igreja->slugPublicoMusicos()]);

        $igreja->setAttribute('link_publico', $linkPublico);
        $igreja->setAttribute('link_publico_musicos', $linkPublicoMusicos);
        $igreja->setAttribute(
            'qr_code_url',
            'https://api.qrserver.com/v1/create-qr-code/?size=260x260&data=' . urlencode($linkPublico)
        );
        $igreja->setAttribute(
            'qr_code_url_musicos',
            'https://api.qrserver.com/v1/create-qr-code/?size=260x260&data=' . urlencode($linkPublicoMusicos)
        );

        return $igreja;
    }

    protected function discoUploadsPublicos(): string
    {
        return (string) config('filesystems.public_uploads_disk', config('filesystems.default'));
    }

    protected function igrejaCombinaComBusca(Igreja $igreja, string $busca): bool
    {
        $termos = preg_split('/\s+/', $this->normalizarTexto($busca), -1, PREG_SPLIT_NO_EMPTY) ?: [];

        if ($termos === []) {
            return true;
        }

        $base = $this->normalizarTexto(implode(' ', [
            $igreja->nome,
            $igreja->cidade,
            $igreja->estado,
            $igreja->endereco,
            $igreja->cnpj,
            $igreja->slug,
        ]));

        foreach ($termos as $termo) {
            if (!str_contains($base, $termo)) {
                return false;
            }
        }

        return true;
    }

    protected function detectarIgrejasParecidas(array $dados)
    {
        $cidade = $this->normalizarTexto((string) ($dados['cidade'] ?? ''));
        $nome = $this->normalizarTexto((string) ($dados['nome'] ?? ''));
        $endereco = $this->normalizarTexto((string) ($dados['endereco'] ?? ''));

        if ($cidade === '' || $nome === '') {
            return collect();
        }

        return Igreja::query()
            ->get()
            ->filter(function (Igreja $igreja) use ($cidade, $nome, $endereco): bool {
                if ($this->normalizarTexto((string) $igreja->cidade) !== $cidade) {
                    return false;
                }

                $nomeExistente = $this->normalizarTexto((string) $igreja->nome);
                $enderecoExistente = $this->normalizarTexto((string) $igreja->endereco);

                similar_text($nome, $nomeExistente, $similaridadeNome);
                similar_text($endereco, $enderecoExistente, $similaridadeEndereco);

                return $similaridadeNome >= 70
                    || ($endereco !== '' && $similaridadeEndereco >= 70)
                    || $this->compartilhaTermosRelevantes($nome, $nomeExistente);
            })
            ->take(5)
            ->map(fn (Igreja $igreja) => [
                'nome' => $igreja->nome,
                'cidade' => $igreja->cidade,
                'estado' => $igreja->estado,
                'endereco' => $igreja->endereco,
            ])
            ->values();
    }

    protected function compartilhaTermosRelevantes(string $textoA, string $textoB): bool
    {
        $termosIgnorados = ['igreja', 'paroquia', 'capela', 'comunidade', 'sao', 'santa', 'nossa', 'senhora', 'de', 'da', 'do', 'dos', 'das'];
        $termos = collect(preg_split('/\s+/', $textoA, -1, PREG_SPLIT_NO_EMPTY) ?: [])
            ->reject(fn (string $termo) => mb_strlen($termo) < 4 || in_array($termo, $termosIgnorados, true))
            ->values();

        if ($termos->isEmpty()) {
            return false;
        }

        return $termos->contains(fn (string $termo) => str_contains($textoB, $termo));
    }

    protected function normalizarTexto(string $texto): string
    {
        return mb_strtolower(Str::ascii($texto));
    }

    protected function criarAdminLocalDaIgreja(
        Igreja $igreja,
        array $dados,
        ?Usuario $ator = null,
        string $origem = 'admin_igrejas_admin_local',
        ?Usuario $usuarioBase = null
    ): Usuario {
        return $this->gestaoUsuariosIgrejaService->criarOuAtualizarContaOperacional(
            dados: [
                'nome' => $dados['admin_nome'],
                'cpf' => $dados['admin_cpf'],
                'email' => $dados['admin_email'],
                'telefone' => $dados['admin_telefone'] ?? null,
                'ativo' => true,
            ],
            igreja: $igreja,
            papeis: [PapelIgreja::ADMIN_LOCAL],
            ator: $ator,
            usuarioBase: $usuarioBase,
            origem: $origem
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
        $deveCadastrarAgora = (bool) $this->normalizarOpcaoAdminLocal($request->input('criar_admin_local_agora'));

        if (!$deveCadastrarAgora) {
            return;
        }

        $campos = [
            'admin_nome',
            'admin_cpf',
            'admin_email',
            'admin_telefone',
        ];

        $preenchidos = collect($campos)->contains(fn (string $campo) => filled($request->input($campo)));

        Validator::make($request->all(), [
            'admin_nome' => ['required', 'string', 'max:255'],
            'admin_cpf' => ['required', 'string', 'max:14'],
            'admin_email' => ['required', 'email', 'max:255'],
        ], [
            'admin_nome.required' => $preenchidos
                ? 'Informe o nome do admin local para concluir o cadastro agora.'
                : 'Voce escolheu cadastrar o admin local agora. Informe o nome do admin local.',
            'admin_cpf.required' => $preenchidos
                ? 'Informe o CPF do admin local para concluir o cadastro agora.'
                : 'Voce escolheu cadastrar o admin local agora. Informe o CPF do admin local.',
            'admin_email.required' => $preenchidos
                ? 'Informe o e-mail do admin local para concluir o cadastro agora.'
                : 'Voce escolheu cadastrar o admin local agora. Informe o e-mail do admin local.',
        ])->validate();
    }

    protected function normalizarOpcaoAdminLocal(mixed $valor): bool
    {
        return match (true) {
            is_bool($valor) => $valor,
            is_numeric($valor) => (int) $valor === 1,
            is_string($valor) => in_array(mb_strtolower(trim($valor)), ['1', 'true', 'sim', 'on', 'yes'], true),
            default => false,
        };
    }
}
