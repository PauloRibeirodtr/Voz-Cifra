<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Igreja;
use App\Models\Usuario;
use App\Services\GestaoUsuariosIgrejaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PadreController extends Controller
{
    public function __construct(
        private readonly GestaoUsuariosIgrejaService $gestaoUsuariosIgrejaService
    ) {
    }

    public function index(): View
    {
        return view('admin.padres.index', [
            'padres' => Usuario::query()
                ->where('eh_padre', true)
                ->with('igreja')
                ->orderBy('nome')
                ->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.padres.create', [
            'padre' => new Usuario([
                'eh_padre' => true,
                'perfil_global' => 'usuario',
                'ativo' => true,
            ]),
            'igrejas' => Igreja::query()->orderBy('nome')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $dados = $this->validarPadre($request);

        $this->gestaoUsuariosIgrejaService->criarOuAtualizarPadre(
            dados: $dados,
            ator: Auth::user(),
            origem: 'admin_padres_store'
        );

        return redirect()
            ->route('admin.padres.index')
            ->with('success', 'Celebrante cadastrado com sucesso.');
    }

    public function edit(Usuario $padre): View
    {
        abort_unless($padre->eh_padre, 404);

        return view('admin.padres.edit', [
            'padre' => $padre,
            'igrejas' => Igreja::query()->orderBy('nome')->get(),
        ]);
    }

    public function update(Request $request, Usuario $padre): RedirectResponse
    {
        abort_unless($padre->eh_padre, 404);
        $dados = $this->validarPadre($request);

        $this->gestaoUsuariosIgrejaService->criarOuAtualizarPadre(
            dados: $dados,
            ator: Auth::user(),
            usuarioBase: $padre,
            origem: 'admin_padres_update'
        );

        return redirect()
            ->route('admin.padres.index')
            ->with('success', 'Celebrante atualizado com sucesso.');
    }

    public function toggle(Usuario $padre): RedirectResponse
    {
        abort_unless($padre->eh_padre, 404);

        $padre = $this->gestaoUsuariosIgrejaService->alterarStatusConta(
            usuario: $padre,
            ativo: !$padre->ativo,
            ator: Auth::user(),
            contexto: [
                'origem' => 'admin_padres_toggle',
                'igreja_id' => $padre->igrejaAtiva()?->id ?? $padre->igreja_id,
                'igreja_nome' => $padre->igrejaAtiva()?->nome ?? $padre->igreja?->nome,
            ]
        );

        return redirect()
            ->route('admin.padres.index')
            ->with('success', $padre->ativo ? 'Celebrante ativado com sucesso.' : 'Celebrante inativado com sucesso.');
    }

    protected function validarPadre(Request $request): array
    {
        return $request->validate([
            'nome' => ['required', 'string', 'max:255'],
            'cpf' => ['required', 'string', 'max:14'],
            'igreja_id' => ['nullable', 'exists:igrejas,id'],
            'ativo' => ['nullable', 'boolean'],
        ]);
    }
}
