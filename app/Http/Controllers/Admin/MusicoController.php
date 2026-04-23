<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PapelIgreja;
use App\Http\Controllers\Controller;
use App\Models\Igreja;
use App\Models\Usuario;
use App\Rules\StrongPassword;
use App\Services\GestaoUsuariosIgrejaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MusicoController extends Controller
{
    public function __construct(
        private readonly GestaoUsuariosIgrejaService $gestaoUsuariosIgrejaService
    ) {
    }

    public function index(): View
    {
        return view('admin.musicos.index', [
            'musicos' => Usuario::query()
                ->with('igreja')
                ->whereHas('papeisAtivosPorIgreja', fn ($subQuery) => $subQuery->where('papel', PapelIgreja::MUSICO->value))
                ->orderBy('nome')
                ->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.musicos.create', [
            'musico' => new Usuario(),
            'igrejas' => Igreja::query()->orderBy('nome')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $dados = $this->validarMusico($request);
        $igreja = Igreja::query()->findOrFail((int) $dados['igreja_id']);

        $this->gestaoUsuariosIgrejaService->criarOuAtualizarContaOperacional(
            dados: $dados,
            igreja: $igreja,
            papeis: [PapelIgreja::MUSICO],
            ator: Auth::user(),
            origem: 'admin_musicos_store'
        );

        return redirect()
            ->route('admin.musicos.index')
            ->with('success', 'Musico cadastrado com sucesso.');
    }

    public function edit(Usuario $musico): View
    {
        $this->garantirMusico($musico);

        return view('admin.musicos.edit', [
            'musico' => $musico,
            'igrejas' => Igreja::query()->orderBy('nome')->get(),
        ]);
    }

    public function update(Request $request, Usuario $musico): RedirectResponse
    {
        $this->garantirMusico($musico);
        $dados = $this->validarMusico($request, $musico);
        $igreja = Igreja::query()->findOrFail((int) $dados['igreja_id']);

        $this->gestaoUsuariosIgrejaService->criarOuAtualizarContaOperacional(
            dados: $dados,
            igreja: $igreja,
            papeis: [PapelIgreja::MUSICO],
            ator: Auth::user(),
            usuarioBase: $musico,
            origem: 'admin_musicos_update'
        );

        return redirect()
            ->route('admin.musicos.index')
            ->with('success', 'Musico atualizado com sucesso.');
    }

    public function toggle(Usuario $musico): RedirectResponse
    {
        $this->garantirMusico($musico);

        $musico = $this->gestaoUsuariosIgrejaService->alterarStatusConta(
            usuario: $musico,
            ativo: !$musico->ativo,
            ator: Auth::user(),
            contexto: [
                'origem' => 'admin_musicos_toggle',
                'igreja_id' => $musico->igrejaAtiva()?->id ?? $musico->igreja_id,
                'igreja_nome' => $musico->igrejaAtiva()?->nome ?? $musico->igreja?->nome,
                'papel' => PapelIgreja::MUSICO->value,
                'papel_label' => PapelIgreja::MUSICO->label(),
            ]
        );

        return redirect()
            ->route('admin.musicos.index')
            ->with('success', $musico->ativo ? 'Musico ativado com sucesso.' : 'Musico inativado com sucesso.');
    }

    public function resetPassword(Usuario $musico): RedirectResponse
    {
        $this->garantirMusico($musico);

        $this->gestaoUsuariosIgrejaService->redefinirSenhaProvisoria(
            usuario: $musico,
            senha: null,
            ator: Auth::user(),
            contexto: [
                'origem' => 'admin_musicos_reset',
                'igreja_id' => $musico->igrejaAtiva()?->id ?? $musico->igreja_id,
                'igreja_nome' => $musico->igrejaAtiva()?->nome ?? $musico->igreja?->nome,
            ]
        );

        return redirect()
            ->route('admin.musicos.index')
            ->with('success', 'Senha redefinida com sucesso. O usuario devera trocar no proximo acesso.');
    }

    public function destroy(Usuario $musico): RedirectResponse
    {
        $this->garantirMusico($musico);

        $this->gestaoUsuariosIgrejaService->alterarStatusConta(
            usuario: $musico,
            ativo: false,
            ator: Auth::user(),
            contexto: [
                'origem' => 'admin_musicos_destroy',
                'igreja_id' => $musico->igrejaAtiva()?->id ?? $musico->igreja_id,
                'igreja_nome' => $musico->igrejaAtiva()?->nome ?? $musico->igreja?->nome,
                'papel' => PapelIgreja::MUSICO->value,
                'papel_label' => PapelIgreja::MUSICO->label(),
            ]
        );

        return redirect()
            ->route('admin.musicos.index')
            ->with('success', 'Conta do musico inativada com sucesso.');
    }

    protected function validarMusico(Request $request, ?Usuario $musico = null): array
    {
        return $request->validate([
            'igreja_id' => ['required', 'exists:igrejas,id'],
            'nome' => ['required', 'string', 'max:255'],
            'cpf' => ['required', 'string', 'max:14'],
            'email' => ['required', 'email', 'max:255'],
            'telefone' => ['nullable', 'string', 'max:20'],
            'password' => ['nullable', 'confirmed', new StrongPassword()],
            'ativo' => ['nullable', 'boolean'],
        ]);
    }

    protected function garantirMusico(Usuario $musico): void
    {
        abort_unless(
            $musico->temPapelNaIgreja(PapelIgreja::MUSICO),
            404
        );
    }
}
