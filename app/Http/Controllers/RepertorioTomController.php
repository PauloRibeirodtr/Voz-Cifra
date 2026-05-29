<?php

namespace App\Http\Controllers;

use App\Enums\PapelIgreja;
use App\Models\MissaMusica;
use App\Models\SolicitacaoMudancaTom;
use App\Models\Usuario;
use App\Services\AuditoriaOperacionalService;
use App\Services\NotificacaoInternaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class RepertorioTomController extends Controller
{
    public function __construct(
        private readonly AuditoriaOperacionalService $auditoriaOperacionalService,
        private readonly NotificacaoInternaService $notificacaoInternaService
    ) {
    }

    public function solicitar(Request $request, MissaMusica $missaMusica): RedirectResponse
    {
        $usuario = Auth::user();
        abort_unless($usuario, 403);

        $missaMusica->loadMissing(['missa.igreja', 'musica', 'versaoMusical']);
        $missa = $missaMusica->missa;
        $igreja = $missa?->igreja;

        abort_unless($missa && $igreja, 404);
        abort_unless((int) ($usuario->igrejaAtivaId() ?? $usuario->igreja_id) === (int) $igreja->id, 403);
        abort_unless($usuario->temPapelNaIgreja(PapelIgreja::MUSICO, $igreja->id)
            || $usuario->temPapelNaIgreja(PapelIgreja::COORDENADOR, $igreja->id)
            || $usuario->temPapelNaIgreja(PapelIgreja::ADMIN_LOCAL, $igreja->id), 403);

        $dados = $request->validate([
            'tom_sugerido' => ['required', Rule::in(config('musical.tons', []))],
            'observacao' => ['nullable', 'string', 'max:500'],
        ], [
            'tom_sugerido.required' => 'Escolha o tom sugerido.',
            'tom_sugerido.in' => 'Escolha um tom padronizado da lista.',
        ]);

        $tomAtual = $missaMusica->tom_exibicao;
        if ($tomAtual && strcasecmp((string) $tomAtual, (string) $dados['tom_sugerido']) === 0) {
            return back()->with('info', 'Este ja e o tom atual da musica na missa.');
        }

        $solicitacao = DB::transaction(function () use ($missaMusica, $missa, $igreja, $usuario, $dados, $tomAtual): SolicitacaoMudancaTom {
            /** @var SolicitacaoMudancaTom $solicitacao */
            $solicitacao = SolicitacaoMudancaTom::query()->updateOrCreate(
                [
                    'missa_musica_id' => $missaMusica->id,
                    'usuario_id' => $usuario->id,
                    'status' => SolicitacaoMudancaTom::STATUS_PENDENTE,
                ],
                [
                    'missa_id' => $missa->id,
                    'igreja_id' => $igreja->id,
                    'tom_atual' => $tomAtual,
                    'tom_sugerido' => $dados['tom_sugerido'],
                    'observacao' => trim((string) ($dados['observacao'] ?? '')) ?: null,
                ]
            );

            return $solicitacao->fresh(['missa', 'missaMusica.musica', 'igreja', 'usuario']);
        });

        $destinatarios = $igreja->usuariosComPapel(PapelIgreja::ADMIN_LOCAL)
            ->where('usuarios.ativo', true)
            ->get()
            ->merge($igreja->usuariosComPapel(PapelIgreja::COORDENADOR)->where('usuarios.ativo', true)->get())
            ->merge(Usuario::query()
                ->where('ativo', true)
                ->where(function ($query): void {
                    $query->where('perfil_global', 'admin_master')
                        ->orWhere('nivel_global', '>=', 6);
                })
                ->get())
            ->unique('id')
            ->values();

        $atorPodeRevisar = $usuario->ehAdminMaster()
            || $usuario->temPapelNaIgreja(PapelIgreja::ADMIN_LOCAL, $igreja->id)
            || $usuario->temPapelNaIgreja(PapelIgreja::COORDENADOR, $igreja->id);

        if (!$atorPodeRevisar) {
            $destinatarios = $destinatarios->reject(fn ($destinatario) => (int) $destinatario->id === (int) $usuario->id);
        }

        foreach ($destinatarios as $destinatario) {
            $this->notificacaoInternaService->pedidoMudancaTomCriado($destinatario, $solicitacao, $usuario);
        }

        $this->auditoriaOperacionalService->registrar(
            evento: 'pedido_mudanca_tom_criado',
            ator: $usuario,
            igreja: $igreja,
            contexto: [
                'origem' => 'member_repertorio_tom',
                'origem_id' => $solicitacao->id,
                'missa_id' => $missa->id,
                'missa_musica_id' => $missaMusica->id,
                'musica_id' => $missaMusica->musica_id,
                'titulo' => $missaMusica->musica?->titulo,
                'tom_atual' => $tomAtual,
                'tom_sugerido' => $solicitacao->tom_sugerido,
                'resumo' => 'Musico solicitou mudanca de tom no repertorio.',
            ]
        );

        return back()->with('success', 'Pedido de mudanca de tom enviado para a equipe da igreja.');
    }

    public function aprovar(Request $request, SolicitacaoMudancaTom $solicitacao): RedirectResponse
    {
        return $this->revisar($solicitacao, SolicitacaoMudancaTom::STATUS_APROVADA, null, $request->input('voltar_para'));
    }

    public function recusar(Request $request, SolicitacaoMudancaTom $solicitacao): RedirectResponse
    {
        $dados = $request->validate([
            'resposta' => ['nullable', 'string', 'max:500'],
        ]);

        return $this->revisar($solicitacao, SolicitacaoMudancaTom::STATUS_RECUSADA, $dados['resposta'] ?? null, $request->input('voltar_para'));
    }

    private function revisar(SolicitacaoMudancaTom $solicitacao, string $status, ?string $resposta = null, ?string $voltarPara = null): RedirectResponse
    {
        $usuario = Auth::user();
        abort_unless($usuario, 403);

        $solicitacao->loadMissing(['missaMusica.musica', 'missa', 'igreja', 'usuario']);
        $igreja = $solicitacao->igreja;
        $missaMusica = $solicitacao->missaMusica;

        abort_unless($igreja && $missaMusica, 404);
        $ehAdminMaster = method_exists($usuario, 'ehAdminMaster') && $usuario->ehAdminMaster();
        abort_unless(
            $ehAdminMaster || (int) ($usuario->igrejaAtivaId() ?? $usuario->igreja_id) === (int) $igreja->id,
            403
        );
        abort_unless(
            $ehAdminMaster
            || $usuario->temPapelNaIgreja(PapelIgreja::ADMIN_LOCAL, $igreja->id)
            || $usuario->temPapelNaIgreja(PapelIgreja::COORDENADOR, $igreja->id),
            403
        );
        abort_unless($solicitacao->estaPendente(), 422);

        DB::transaction(function () use ($solicitacao, $status, $resposta, $usuario, $missaMusica): void {
            if ($status === SolicitacaoMudancaTom::STATUS_APROVADA) {
                $missaMusica->update([
                    'tom_usado' => $solicitacao->tom_sugerido,
                ]);
            }

            $solicitacao->update([
                'status' => $status,
                'resposta' => trim((string) $resposta) ?: null,
                'revisado_por' => $usuario->id,
                'revisado_em' => now(),
            ]);
        });

        $solicitacao->refresh()->loadMissing(['missaMusica.musica', 'missa', 'igreja', 'usuario']);

        $this->notificacaoInternaService->pedidoMudancaTomRespondido($solicitacao, $usuario);

        $this->auditoriaOperacionalService->registrar(
            evento: $status === SolicitacaoMudancaTom::STATUS_APROVADA
                ? 'pedido_mudanca_tom_aprovado'
                : 'pedido_mudanca_tom_recusado',
            ator: $usuario,
            igreja: $igreja,
            contexto: [
                'origem' => 'local_admin_repertorio_tom',
                'origem_id' => $solicitacao->id,
                'missa_id' => $solicitacao->missa_id,
                'missa_musica_id' => $solicitacao->missa_musica_id,
                'musica_id' => $missaMusica->musica_id,
                'titulo' => $missaMusica->musica?->titulo,
                'tom_anterior' => $solicitacao->tom_atual,
                'tom_sugerido' => $solicitacao->tom_sugerido,
                'resumo' => $status === SolicitacaoMudancaTom::STATUS_APROVADA
                    ? 'Pedido de mudanca de tom aprovado e aplicado ao repertorio.'
                    : 'Pedido de mudanca de tom recusado.',
            ]
        );

        $mensagem = $status === SolicitacaoMudancaTom::STATUS_APROVADA
            ? 'Pedido aprovado. O tom da missa foi atualizado.'
            : 'Pedido recusado e musico notificado.';

        if ($voltarPara === 'back') {
            return back()->with('success', $mensagem);
        }

        return redirect()
            ->to(route('local-admin.missas.show', $solicitacao->missa_id) . '#repertorio-item-' . $solicitacao->missa_musica_id)
            ->with('success', $mensagem);
    }
}
