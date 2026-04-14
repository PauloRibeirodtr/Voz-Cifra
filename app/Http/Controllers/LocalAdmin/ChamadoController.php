<?php

namespace App\Http\Controllers\LocalAdmin;

use App\Http\Controllers\Controller;
use App\Models\Chamado;
use App\Models\Igreja;
use App\Models\Usuario;
use App\Services\ChamadoSupportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ChamadoController extends Controller
{
    public function __construct(
        private readonly ChamadoSupportService $supportService,
    ) {
    }

    public function createPedidoAcesso(Request $request): View
    {
        $adminLocal = $this->obterUsuario();
        $igreja = $this->obterIgreja($adminLocal);
        $musicoSelecionadoId = (int) $request->integer('musico');

        $musicos = Usuario::query()
            ->where('igreja_id', $igreja->id)
            ->where('perfil_global', 'member')
            ->orderBy('nome')
            ->get();

        $recentes = Chamado::query()
            ->where('categoria', 'pedido_acesso_musico')
            ->where('solicitante_usuario_id', $adminLocal->id)
            ->with('responsavel')
            ->latest()
            ->take(6)
            ->get();

        return view('local-admin.chamados.pedido-acesso', [
            'adminLocal' => $adminLocal,
            'igreja' => $igreja,
            'musicos' => $musicos,
            'musicoSelecionadoId' => $musicoSelecionadoId,
            'recentes' => $recentes,
            'supportService' => $this->supportService,
        ]);
    }

    public function storePedidoAcesso(Request $request): RedirectResponse
    {
        $adminLocal = $this->obterUsuario();
        $igreja = $this->obterIgreja($adminLocal);

        $dados = $request->validate([
            'musico_id' => ['required', 'integer'],
            'motivo' => ['required', 'string', 'min:10', 'max:2000'],
        ], [
            'musico_id.required' => 'Escolha o musico que precisa receber acesso.',
            'motivo.required' => 'Explique rapidamente por que esse acesso esta sendo solicitado.',
            'motivo.min' => 'Escreva pelo menos 10 caracteres para contextualizar o suporte.',
        ]);

        /** @var Usuario|null $musico */
        $musico = Usuario::query()
            ->whereKey($dados['musico_id'])
            ->where('igreja_id', $igreja->id)
            ->where('perfil_global', 'member')
            ->first();

        abort_unless($musico !== null, 404);

        if ($musico->ativo && !$musico->primeiro_acesso) {
            return back()
                ->withInput()
                ->withErrors(['musico_id' => 'Esse musico ja possui acesso ativo e nao precisa de uma nova solicitacao.']);
        }

        if ($this->supportService->possuiPedidoAcessoAbertoParaMusico($musico->id)) {
            return back()
                ->withInput()
                ->withErrors(['musico_id' => 'Ja existe um pedido de acesso em andamento para esse musico.']);
        }

        $chamado = $this->supportService->abrirPedidoAcessoMusico(
            $adminLocal,
            $musico,
            $dados['motivo']
        );

        return redirect()
            ->route('local-admin.chamados.acesso.create', ['musico' => $musico->id])
            ->with('success', 'Pedido enviado ao suporte com sucesso. Protocolo: ' . $chamado->protocolo);
    }

    private function obterUsuario(): Usuario
    {
        /** @var \App\Models\Usuario $usuario */
        $usuario = Auth::user();

        abort_unless($usuario && $usuario->ehAdminLocal(), 403);

        return $usuario;
    }

    private function obterIgreja(Usuario $usuario): Igreja
    {
        $igreja = $usuario->igreja;

        abort_unless($igreja !== null, 404, 'Igreja nao encontrada para este administrador local.');

        return $igreja;
    }
}
