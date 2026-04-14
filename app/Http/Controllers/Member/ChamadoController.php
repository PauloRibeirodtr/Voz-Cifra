<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Chamado;
use App\Models\Usuario;
use App\Services\ChamadoSupportService;
use App\Services\SuporteTelegramService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ChamadoController extends Controller
{
    public function __construct(
        private readonly ChamadoSupportService $supportService,
        private readonly SuporteTelegramService $telegramService,
    ) {
        $this->middleware(['auth', 'verified_custom', 'role:member']);
    }

    public function index(): View
    {
        $usuario = $this->obterUsuario();

        $chamados = Chamado::query()
            ->with('responsavel')
            ->withCount([
                'mensagens as mensagens_publicas_count' => fn ($query) => $query->where('interno', false),
            ])
            ->where('solicitante_usuario_id', $usuario->id)
            ->orderByRaw("case when status = 'aberto' then 0 when status = 'em_andamento' then 1 when status = 'aguardando_usuario' then 2 when status = 'resolvido' then 3 else 4 end")
            ->orderByDesc('ultima_interacao_em')
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('member.chamados.index', [
            'usuario' => $usuario,
            'chamados' => $chamados,
            'supportService' => $this->supportService,
            'telegramBaseUrl' => $this->telegramService->gerarUrl(),
        ]);
    }

    public function create(): View
    {
        return view('member.chamados.create', [
            'supportService' => $this->supportService,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $usuario = $this->obterUsuario();

        $dados = $request->validate([
            'categoria' => ['required', 'in:acorde,contestacao_inativacao,musica,acesso,outro'],
            'descricao' => ['required', 'string', 'min:10', 'max:3000'],
        ], [
            'descricao.min' => 'Explique um pouco mais para o suporte conseguir agir sem te pedir tudo de novo.',
        ]);

        $chamado = $this->supportService->abrirChamadoDoMusico(
            $usuario,
            $dados['categoria'],
            $dados['descricao']
        );

        return redirect()
            ->route('member.chamados.show', $chamado)
            ->with('success', 'Chamado aberto com sucesso. Protocolo: ' . $chamado->protocolo);
    }

    public function show(Chamado $chamado): View
    {
        $usuario = $this->obterUsuario();
        abort_unless((int) $chamado->solicitante_usuario_id === (int) $usuario->id, 403);

        $chamado->load([
            'responsavel',
            'auditoriaEvento',
            'mensagens' => fn ($query) => $query->where('interno', false)->orderBy('created_at'),
        ]);

        return view('member.chamados.show', [
            'usuario' => $usuario,
            'chamado' => $chamado,
            'supportService' => $this->supportService,
            'telegramUrl' => $this->telegramService->gerarUrl($chamado->protocolo),
        ]);
    }

    public function avaliar(Request $request, Chamado $chamado): RedirectResponse
    {
        $usuario = $this->obterUsuario();
        abort_unless((int) $chamado->solicitante_usuario_id === (int) $usuario->id, 403);

        abort_unless(in_array($chamado->status, ['resolvido', 'fechado'], true), 422);

        $dados = $request->validate([
            'avaliacao_nota' => ['required', 'integer', 'min:1', 'max:5'],
            'avaliacao_comentario' => ['nullable', 'string', 'max:1000'],
        ]);

        $chamado->forceFill([
            'avaliacao_nota' => (int) $dados['avaliacao_nota'],
            'avaliacao_comentario' => $dados['avaliacao_comentario'] ?? null,
        ])->save();

        return back()->with('success', 'Avaliacao registrada. Obrigado pelo seu retorno.');
    }

    private function obterUsuario(): Usuario
    {
        /** @var \App\Models\Usuario $usuario */
        $usuario = Auth::user();

        abort_unless($usuario && $usuario->ehMembro(), 403);

        return $usuario;
    }
}
