<?php

namespace App\Http\Controllers\Coordenador;

use App\Enums\PapelIgreja;
use App\Http\Controllers\Controller;
use App\Models\Igreja;
use App\Models\Usuario;
use App\Services\GestaoUsuariosIgrejaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GestaoIgrejaController extends Controller
{
    public function __construct(
        private readonly GestaoUsuariosIgrejaService $gestaoUsuariosIgrejaService
    ) {
    }

    public function storeAdminLocal(Request $request): RedirectResponse
    {
        $dados = $request->validate([
            'nome' => ['required', 'string', 'max:255'],
            'cpf' => ['required', 'string', 'max:14'],
            'email' => ['required', 'email', 'max:255'],
            'telefone' => ['nullable', 'string', 'max:20'],
        ]);

        $igreja = $this->obterIgreja();

        $this->gestaoUsuariosIgrejaService->criarOuAtualizarContaOperacional(
            dados: [
                'nome' => $dados['nome'],
                'cpf' => $dados['cpf'],
                'email' => $dados['email'],
                'telefone' => $dados['telefone'] ?? null,
                'ativo' => true,
            ],
            igreja: $igreja,
            papeis: [PapelIgreja::ADMIN_LOCAL],
            ator: Auth::user(),
            origem: 'coordenador_admins_locais_store'
        );

        return redirect()
            ->route('coordenador.dashboard')
            ->with('success', 'Admin local atribuido a igreja com sucesso.');
    }

    protected function obterUsuario(): Usuario
    {
        /** @var Usuario $usuario */
        $usuario = Auth::user();

        abort_unless($usuario && $usuario->ehCoordenador(), 403);

        return $usuario;
    }

    protected function obterIgreja(): Igreja
    {
        $igreja = $this->obterUsuario()->igrejaAtiva() ?? $this->obterUsuario()->igreja;

        abort_unless($igreja !== null, 404);

        return $igreja;
    }
}
