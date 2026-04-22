<?php

namespace App\Http\Controllers\LocalAdmin;

use App\Enums\PapelIgreja;
use App\Http\Controllers\Controller;
use App\Models\Igreja;
use App\Models\Usuario;
use App\Rules\StrongPassword;
use App\Services\GestaoUsuariosIgrejaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class MusicoController extends Controller
{
    public function __construct(
        private readonly GestaoUsuariosIgrejaService $gestaoUsuariosIgrejaService
    ) {
    }

    public function index(): View
    {
        $igreja = $this->obterIgreja();

        return view('local-admin.musicos.index', [
            'igreja' => $igreja,
            'usuariosIgreja' => $igreja->musicos()
                ->orderBy('nome')
                ->get(),
        ]);
    }

    public function create(): View
    {
        return view('local-admin.musicos.create', [
            'igreja' => $this->obterIgreja(),
            'musico' => new Usuario(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $dados = $this->validarMusico($request);
        $igreja = $this->obterIgreja();

        $this->gestaoUsuariosIgrejaService->criarOuAtualizarContaOperacional(
            dados: $dados,
            igreja: $igreja,
            papeis: [PapelIgreja::MUSICO],
            ator: Auth::user(),
            origem: $this->ehCoordenador() ? 'coordenador_musicos_store' : 'local_admin_musicos_store'
        );

        return redirect()
            ->route($this->routeName('musicos.index'))
            ->with('success', 'Musico cadastrado com sucesso.');
    }

    public function edit(Usuario $musico): View
    {
        $this->garantirMusicoDaIgreja($musico);

        return view('local-admin.musicos.edit', [
            'igreja' => $this->obterIgreja(),
            'musico' => $musico,
        ]);
    }

    public function update(Request $request, Usuario $musico): RedirectResponse
    {
        $this->garantirMusicoDaIgreja($musico);
        $dados = $this->validarMusico($request, $musico);

        $this->gestaoUsuariosIgrejaService->criarOuAtualizarContaOperacional(
            dados: $dados,
            igreja: $this->obterIgreja(),
            papeis: [PapelIgreja::MUSICO],
            ator: Auth::user(),
            usuarioBase: $musico,
            origem: $this->ehCoordenador() ? 'coordenador_musicos_update' : 'local_admin_musicos_update'
        );

        return redirect()
            ->route($this->routeName('musicos.index'))
            ->with('success', 'Musico atualizado com sucesso.');
    }

    public function vincularExistente(Request $request): RedirectResponse
    {
        $dados = $request->validate([
            'usuario_id' => ['nullable', 'integer', 'exists:usuarios,id', 'required_without_all:cpf,email'],
            'cpf' => ['nullable', 'string', 'max:14', 'required_without_all:usuario_id,email'],
            'email' => ['nullable', 'email', 'max:255', 'required_without_all:usuario_id,cpf'],
        ]);

        $this->gestaoUsuariosIgrejaService->vincularUsuarioExistente(
            dados: $dados,
            igreja: $this->obterIgreja(),
            papeis: [PapelIgreja::MUSICO],
            ator: Auth::user(),
            origem: $this->ehCoordenador() ? 'coordenador_musicos_vincular_existente' : 'local_admin_musicos_vincular_existente'
        );

        return redirect()
            ->route($this->routeName('musicos.index'))
            ->with('success', 'Musico existente vinculado a igreja com sucesso.');
    }

    public function toggle(Usuario $musico): RedirectResponse
    {
        $this->garantirMusicoDaIgreja($musico);

        return back()->withErrors([
            'musico' => 'Somente o admin master pode inativar ou reativar contas.',
        ]);
    }

    public function resetPassword(Usuario $musico): RedirectResponse
    {
        $this->garantirMusicoDaIgreja($musico);

        $this->gestaoUsuariosIgrejaService->redefinirSenhaProvisoria(
            usuario: $musico,
            senha: null,
            ator: Auth::user(),
            contexto: [
                'origem' => $this->ehCoordenador() ? 'coordenador_musicos_reset' : 'local_admin_musicos_reset',
                'igreja_id' => $this->obterIgreja()->id,
                'igreja_nome' => $this->obterIgreja()->nome,
            ]
        );

        return redirect()
            ->route($this->routeName('musicos.index'))
            ->with('success', 'Senha redefinida com sucesso. O usuario devera trocar no proximo acesso.');
    }

    public function destroy(Usuario $musico): RedirectResponse
    {
        $this->garantirMusicoDaIgreja($musico);

        return back()->withErrors([
            'musico' => 'Somente o admin master pode inativar contas. O vinculo do musico sera tratado em uma etapa posterior.',
        ]);
    }

    protected function validarMusico(Request $request, ?Usuario $musico = null): array
    {
        return $request->validate([
            'nome' => ['required', 'string', 'max:255'],
            'cpf' => ['required', 'string', 'max:14'],
            'email' => ['required', 'email', 'max:255'],
            'telefone' => ['nullable', 'string', 'max:20'],
            'password' => ['nullable', 'confirmed', new StrongPassword()],
            'ativo' => ['nullable', 'boolean'],
        ]);
    }

    protected function obterUsuario(): Usuario
    {
        /** @var \App\Models\Usuario $usuario */
        $usuario = Auth::user();

        abort_unless($usuario && ($usuario->ehAdminLocal() || $usuario->ehCoordenador()), 403);

        return $usuario;
    }

    protected function obterIgreja(): Igreja
    {
        $igreja = $this->obterUsuario()->igrejaAtiva() ?? $this->obterUsuario()->igreja;

        abort_unless($igreja !== null, 404);

        return $igreja;
    }

    protected function garantirMusicoDaIgreja(Usuario $musico): void
    {
        $igreja = $this->obterIgreja();

        abort_unless(
            $musico->temPapelNaIgreja(PapelIgreja::MUSICO, $igreja->id) || ((int) $musico->igreja_id === (int) $igreja->id && $musico->perfil_global === 'member'),
            404
        );
    }

    protected function routeName(string $sufixo): string
    {
        $nomeAtual = request()->route()?->getName() ?? '';

        if (str_starts_with($nomeAtual, 'coordenador.')) {
            return 'coordenador.' . $sufixo;
        }

        return 'local-admin.' . $sufixo;
    }

    protected function ehCoordenador(): bool
    {
        return $this->obterUsuario()->ehCoordenador();
    }
}
