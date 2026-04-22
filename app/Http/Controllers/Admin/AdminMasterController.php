<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PapelIgreja;
use App\Http\Controllers\Controller;
use App\Models\Acorde;
use App\Models\Igreja;
use App\Models\Missa;
use App\Models\Musica;
use App\Models\Usuario;
use App\Models\UsuarioIgrejaPapel;
use App\Rules\StrongPassword;
use App\Services\GestaoUsuariosIgrejaService;
use App\Services\NotificacaoSegurancaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminMasterController extends Controller
{
    public function __construct(
        private readonly NotificacaoSegurancaService $notificacaoSegurancaService,
        private readonly GestaoUsuariosIgrejaService $gestaoUsuariosIgrejaService
    ) {
    }

    public function dashboard()
    {
        $metricas = [
            'total_usuarios' => Usuario::count(),
            'total_igrejas' => Igreja::count(),
            'total_musicas' => Musica::count(),
            'total_missas' => Missa::count(),
            'admins_locais' => UsuarioIgrejaPapel::query()
                ->where('papel', PapelIgreja::ADMIN_LOCAL->value)
                ->where('ativo', true)
                ->count(),
            'musicos' => UsuarioIgrejaPapel::query()
                ->where('papel', PapelIgreja::MUSICO->value)
                ->where('ativo', true)
                ->count(),
        ];

        return view('admin.dashboard', [
            'metrics' => $metricas,
        ]);
    }

    public function hierarchy(): View
    {
        /** @var \App\Models\Usuario $usuario */
        $usuario = Auth::user();
        $nivelUsuario = method_exists($usuario, 'nivelGlobal') ? $usuario->nivelGlobal() : 1;

        abort_unless($nivelUsuario >= 7, 403, 'A hierarquia de usuarios esta disponivel apenas para nivel global 7.');

        $adminsMasterAbaixo = Usuario::query()
            ->with('igreja')
            ->where('perfil_global', 'admin_master')
            ->where('id', '!=', $usuario->id)
            ->where('nivel_global', '<', $nivelUsuario)
            ->orderByDesc('nivel_global')
            ->orderBy('nome')
            ->get();

        $adminsLocais = Usuario::query()
            ->with('igreja')
            ->where('perfil_global', 'admin_local')
            ->orderBy('nome')
            ->get()
            ->groupBy(fn (Usuario $item): string => $item->igreja?->nome ?? 'Sem igreja vinculada');

        $membros = Usuario::query()
            ->with('igreja')
            ->where('perfil_global', 'member')
            ->orderBy('nome')
            ->get()
            ->groupBy(fn (Usuario $item): string => $item->igreja?->nome ?? 'Sem igreja vinculada');

        return view('admin.users.hierarchy', [
            'usuarioAtual' => $usuario,
            'nivelUsuarioAtual' => $nivelUsuario,
            'adminsMasterAbaixo' => $adminsMasterAbaixo,
            'adminsLocaisPorIgreja' => $adminsLocais,
            'membrosPorIgreja' => $membros,
            'totais' => [
                'admins_master_abaixo' => $adminsMasterAbaixo->count(),
                'igrejas_com_admin_local' => $adminsLocais->count(),
                'admins_locais' => $adminsLocais->flatten(1)->count(),
                'igrejas_com_membros' => $membros->count(),
                'membros' => $membros->flatten(1)->count(),
            ],
        ]);
    }

    public function settings(): View
    {
        return view('admin.settings.index', [
            'metricasSistema' => [
                'total_igrejas' => Igreja::count(),
                'total_musicas' => Musica::count(),
                'total_acordes' => Acorde::count(),
                'total_usuarios' => Usuario::count(),
            ],
            'adminsMaster' => Usuario::query()
                ->where('perfil_global', 'admin_master')
                ->orderBy('nome')
                ->get(),
        ]);
    }

    public function profile()
    {
        return view('admin.settings.profile', [
            'user' => Auth::user(),
        ]);
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        /** @var \App\Models\Usuario $usuario */
        $usuario = Auth::user();
        $primeiroAcesso = (bool) ($usuario->primeiro_acesso ?? false);

        $dados = $request->validate([
            'email' => ['required', 'email', Rule::unique('usuarios', 'email')->ignore($usuario->id)],
            'telefone' => ['nullable', 'string', 'max:20'],
            'password' => [$primeiroAcesso ? 'required' : 'nullable', 'confirmed', new StrongPassword()],
            'theme_preference' => ['required', Rule::in(['system', 'light', 'dark'])],
        ], [
            'password.required' => 'No primeiro acesso, defina uma nova senha forte para liberar o painel administrativo.',
            'password.confirmed' => 'A confirmacao da senha nao confere.',
        ]);

        $usuario->email = $dados['email'];
        $usuario->telefone = $dados['telefone'] ?? null;
        $usuario->theme_preference = $dados['theme_preference'];

        if (!empty($dados['password'])) {
            $usuario->password = $dados['password'];
            $usuario->primeiro_acesso = false;
        }

        $usuario->save();

        return back()->with('success', $primeiroAcesso
            ? 'Senha atualizada com sucesso. O painel administrativo foi liberado.'
            : 'Perfil atualizado com sucesso.');
    }

    public function storeAdminMaster(Request $request): RedirectResponse
    {
        /** @var \App\Models\Usuario $ator */
        $ator = Auth::user();
        $atorNivel = method_exists($ator, 'nivelGlobal') ? $ator->nivelGlobal() : 6;
        $niveisPermitidos = $atorNivel >= 7 ? [6, 7] : [6];

        $dados = $request->validate([
            'nome' => ['required', 'string', 'max:255'],
            'cpf' => ['required', 'string', 'max:14', Rule::unique('usuarios', 'cpf')],
            'email' => ['required', 'email', 'max:255', Rule::unique('usuarios', 'email')],
            'telefone' => ['nullable', 'string', 'max:20'],
            'password' => ['nullable', 'confirmed', new StrongPassword()],
            'ativo' => ['nullable', 'boolean'],
            'nivel_global' => ['nullable', Rule::in($niveisPermitidos)],
        ]);

        $nivelGlobal = isset($dados['nivel_global']) ? (int) $dados['nivel_global'] : 6;

        $this->gestaoUsuariosIgrejaService->criarOuAtualizarContaBase(
            dados: [
                'nome' => $dados['nome'],
                'cpf' => $dados['cpf'],
                'email' => $dados['email'],
                'telefone' => $dados['telefone'] ?? null,
                'password' => $dados['password'] ?? null,
                'perfil_global' => 'admin_master',
                'nivel_global' => $nivelGlobal,
                'ativo' => (bool) ($dados['ativo'] ?? true),
                'eh_padre' => false,
            ],
            ator: $ator,
            origem: 'admin_settings_store_admin_master'
        );

        return redirect()
            ->route('admin.settings')
            ->with('success', 'Novo admin master cadastrado com sucesso. A senha inicial sera a informada no formulario ou, se ficar em branco, o CPF sem pontuacao.');
    }

    public function updateNivelGlobal(Request $request, Usuario $usuario): RedirectResponse
    {
        /** @var \App\Models\Usuario $ator */
        $ator = Auth::user();
        $atorNivel = method_exists($ator, 'nivelGlobal') ? $ator->nivelGlobal() : 6;

        abort_unless($atorNivel >= 7, 403, 'Apenas nivel global 7 pode alterar nivel global.');
        abort_unless($usuario->perfil_global === 'admin_master', 422, 'Somente admins master podem ter nivel global ajustado nesta etapa.');

        $dados = $request->validate([
            'nivel_global' => ['required', Rule::in([6, 7])],
        ]);

        $nivelAnterior = method_exists($usuario, 'nivelGlobal') ? $usuario->nivelGlobal() : (int) ($usuario->nivel_global ?? 1);
        $nivelNovo = (int) $dados['nivel_global'];

        if (!$this->podeGerenciarUsuarioPorNivel($ator, $usuario)) {
            return back()->withErrors([
                'nivel_global' => 'Voce so pode alterar nivel de usuarios com nivel global inferior ao seu.',
            ]);
        }

        if ($nivelAnterior === $nivelNovo) {
            return back()->with('success', 'Nivel global ja estava configurado com este valor.');
        }

        if ($usuario->id === $ator->id && $nivelNovo < 7) {
            return back()->withErrors([
                'nivel_global' => 'O super admin logado nao pode reduzir o proprio nivel nesta operacao.',
            ]);
        }

        $usuario->nivel_global = $nivelNovo;
        $usuario->save();

        $this->notificacaoSegurancaService->enviarEventoConta(
            alvo: $usuario,
            evento: 'troca_nivel_global',
            ator: $ator,
            contexto: [
                'origem' => 'admin_settings_update_nivel_global',
                'nivel_anterior' => $nivelAnterior,
                'nivel_novo' => $nivelNovo,
            ]
        );

        return back()->with('success', 'Nivel global atualizado com sucesso.');
    }

    public function toggleAdminMaster(Usuario $usuario): RedirectResponse
    {
        /** @var \App\Models\Usuario $ator */
        $ator = Auth::user();
        $atorNivel = method_exists($ator, 'nivelGlobal') ? $ator->nivelGlobal() : 6;

        abort_unless($atorNivel >= 7, 403, 'Apenas nivel global 7 pode ativar/inativar admins master.');
        abort_unless($usuario->perfil_global === 'admin_master', 422, 'Somente admins master podem ser gerenciados nesta operacao.');

        if ($usuario->id === $ator->id) {
            return back()->withErrors([
                'admin_master' => 'Voce nao pode inativar a propria conta nesta operacao.',
            ]);
        }

        if (!$this->podeGerenciarUsuarioPorNivel($ator, $usuario)) {
            return back()->withErrors([
                'admin_master' => 'Voce so pode ativar/inativar usuarios com nivel global inferior ao seu.',
            ]);
        }

        $novoStatus = !$usuario->ativo;
        $usuario->ativo = $novoStatus;
        $usuario->save();

        $this->notificacaoSegurancaService->enviarEventoConta(
            alvo: $usuario,
            evento: $novoStatus ? 'conta_reativada' : 'conta_inativada',
            ator: $ator,
            contexto: [
                'origem' => 'admin_settings_toggle_admin_master',
                'nivel_global' => method_exists($usuario, 'nivelGlobal') ? $usuario->nivelGlobal() : (int) ($usuario->nivel_global ?? 1),
            ]
        );

        return back()->with('success', $novoStatus
            ? 'Admin master reativado com sucesso.'
            : 'Admin master inativado com sucesso.');
    }

    public function resetAdminMasterPassword(Request $request, Usuario $usuario): RedirectResponse
    {
        /** @var \App\Models\Usuario $ator */
        $ator = Auth::user();
        $atorNivel = method_exists($ator, 'nivelGlobal') ? $ator->nivelGlobal() : 6;

        abort_unless($atorNivel >= 7, 403, 'Apenas nivel global 7 pode resetar senha de admins master.');
        abort_unless($usuario->perfil_global === 'admin_master', 422, 'Somente admins master podem ser gerenciados nesta operacao.');

        if (!$this->podeGerenciarUsuarioPorNivel($ator, $usuario)) {
            return back()->withErrors([
                'admin_master' => 'Voce so pode resetar senha de usuarios com nivel global inferior ao seu.',
            ]);
        }

        $dados = $request->validate([
            'password' => ['nullable', 'confirmed', new StrongPassword()],
        ], [
            'password.confirmed' => 'A confirmacao da nova senha nao confere.',
        ]);

        $cpfNumerico = preg_replace('/\D+/', '', (string) $usuario->cpf) ?: (string) $usuario->cpf;
        $senha = filled($dados['password'] ?? null) ? (string) $dados['password'] : $cpfNumerico;

        $usuario->password = $senha;
        $usuario->primeiro_acesso = true;
        $usuario->save();

        $this->notificacaoSegurancaService->enviarEventoConta(
            alvo: $usuario,
            evento: 'reset_senha',
            ator: $ator,
            contexto: [
                'origem' => 'admin_settings_reset_admin_master',
                'senha_inicial' => filled($dados['password'] ?? null) ? 'definida_manual' : 'cpf_sem_pontuacao',
            ]
        );

        return back()->with('success', 'Senha do admin master redefinida com sucesso.');
    }

    private function podeGerenciarUsuarioPorNivel(Usuario $ator, Usuario $alvo): bool
    {
        $nivelAtor = method_exists($ator, 'nivelGlobal') ? $ator->nivelGlobal() : 1;
        $nivelAlvo = method_exists($alvo, 'nivelGlobal') ? $alvo->nivelGlobal() : 1;

        return $nivelAtor > $nivelAlvo;
    }
}
