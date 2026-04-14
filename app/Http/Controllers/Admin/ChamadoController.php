<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Chamado;
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

    public function index(Request $request): View
    {
        $this->autorizarNivel7();

        $status = trim((string) $request->string('status'));
        $prioridade = trim((string) $request->string('prioridade'));
        $categoria = trim((string) $request->string('categoria'));
        $busca = trim((string) $request->string('q'));

        $chamados = $this->supportService
            ->aplicarFiltros(
                Chamado::query()->with(['responsavel', 'solicitante'])->withCount('mensagens'),
                $status,
                $prioridade,
                $categoria,
                $busca
            )
            ->orderByRaw("case when status = 'aberto' then 0 when status = 'em_andamento' then 1 when status = 'aguardando_usuario' then 2 when status = 'resolvido' then 3 else 4 end")
            ->orderByDesc('ultima_interacao_em')
            ->orderByDesc('created_at')
            ->paginate(12)
            ->withQueryString();

        return view('admin.chamados.index', [
            'chamados' => $chamados,
            'filtros' => [
                'status' => $status,
                'prioridade' => $prioridade,
                'categoria' => $categoria,
                'q' => $busca,
            ],
            'metricas' => $this->supportService->metricas(),
            'statusOptions' => $this->supportService->statusOptions(),
            'prioridadeOptions' => $this->supportService->prioridadeOptions(),
            'categoriaOptions' => $this->supportService->categoriaOptions(),
            'supportService' => $this->supportService,
        ]);
    }

    public function show(Chamado $chamado): View
    {
        $this->autorizarNivel7();

        $chamado->load([
            'auditoriaEvento',
            'solicitante',
            'responsavel',
            'mensagens' => fn ($query) => $query->with('autor')->orderBy('created_at'),
        ]);

        return view('admin.chamados.show', [
            'chamado' => $chamado,
            'statusOptions' => $this->supportService->statusOptions(),
            'supportService' => $this->supportService,
            'musicoAlvoPedidoAcesso' => $this->resolverMusicoAlvo($chamado),
        ]);
    }

    public function updateStatus(Request $request, Chamado $chamado): RedirectResponse
    {
        $this->autorizarNivel7();

        $dados = $request->validate([
            'status' => ['required', 'in:aberto,em_andamento,aguardando_usuario,resolvido,fechado'],
            'resolucao_resumo' => ['nullable', 'string', 'max:2000'],
        ]);

        /** @var \App\Models\Usuario|null $usuario */
        $usuario = Auth::user();
        $this->supportService->atualizarStatus(
            $chamado,
            $dados['status'],
            $dados['resolucao_resumo'] ?? null,
            $usuario
        );

        return back()->with('success', 'Status do chamado atualizado com sucesso.');
    }

    public function storeMessage(Request $request, Chamado $chamado): RedirectResponse
    {
        $this->autorizarNivel7();

        $dados = $request->validate([
            'mensagem' => ['required', 'string', 'max:5000'],
            'interno' => ['nullable', 'boolean'],
        ]);

        $usuario = Auth::user();

        $this->supportService->registrarMensagem(
            $chamado,
            $dados['mensagem'],
            (bool) ($dados['interno'] ?? false),
            $usuario
        );

        return back()->with('success', 'Resposta registrada no chamado.');
    }

    public function assumir(Chamado $chamado): RedirectResponse
    {
        $this->autorizarNivel7();

        /** @var \App\Models\Usuario|null $usuario */
        $usuario = Auth::user();
        $this->supportService->assumirChamado($chamado, $usuario);

        return back()->with('success', 'Chamado assumido com sucesso.');
    }

    public function aprovarPedidoAcesso(Chamado $chamado): RedirectResponse
    {
        $this->autorizarNivel7();

        /** @var \App\Models\Usuario|null $usuario */
        $usuario = Auth::user();
        $musico = $this->supportService->aprovarPedidoAcessoMusico($chamado, $usuario);

        if (!$musico) {
            return back()->withErrors([
                'chamado' => 'Nao foi possivel aprovar este pedido de acesso. Verifique se o musico ainda existe e se o chamado continua elegivel.',
            ]);
        }

        return back()->with('success', 'Pedido aprovado. O musico ' . $musico->nome . ' foi liberado para acessar novamente.');
    }

    public function pedirMaisDados(Request $request, Chamado $chamado): RedirectResponse
    {
        $this->autorizarNivel7();

        $dados = $request->validate([
            'mensagem' => ['required', 'string', 'max:2000'],
        ]);

        /** @var \App\Models\Usuario|null $usuario */
        $usuario = Auth::user();
        $this->supportService->pedirMaisDados($chamado, $dados['mensagem'], $usuario);

        return back()->with('success', 'Chamado movido para aguardando usuario.');
    }

    private function autorizarNivel7(): void
    {
        /** @var \App\Models\Usuario|null $usuario */
        $usuario = Auth::user();

        abort_unless($usuario && method_exists($usuario, 'nivelGlobal') && $usuario->nivelGlobal() >= 7, 403);
    }

    private function resolverMusicoAlvo(Chamado $chamado): ?\App\Models\Usuario
    {
        if ($chamado->categoria !== 'pedido_acesso_musico' || $chamado->origem_tipo !== 'usuario' || !$chamado->origem_id) {
            return null;
        }

        return \App\Models\Usuario::query()
            ->whereKey($chamado->origem_id)
            ->where('perfil_global', 'member')
            ->first();
    }
}
