<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use App\Services\NotificacaoSegurancaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AdminLocalController extends Controller
{
    public function __construct(
        private readonly NotificacaoSegurancaService $notificacaoSegurancaService,
    ) {
    }

    public function index(Request $request): View
    {
        $this->autorizarNivel7();

        $busca = trim((string) $request->string('q'));
        $status = trim((string) $request->string('status'));

        $adminsLocais = Usuario::query()
            ->with('igreja')
            ->where('perfil_global', 'admin_local')
            ->when($status !== '', fn ($query) => $query->where('ativo', $status === 'ativo'))
            ->when($busca !== '', function ($query) use ($busca): void {
                $query->where(function ($subquery) use ($busca): void {
                    $subquery->where('nome', 'like', '%' . $busca . '%')
                        ->orWhere('email', 'like', '%' . $busca . '%')
                        ->orWhere('cpf', 'like', '%' . $busca . '%')
                        ->orWhereHas('igreja', fn ($igrejas) => $igrejas->where('nome', 'like', '%' . $busca . '%'));
                });
            })
            ->orderBy('nome')
            ->paginate(15)
            ->withQueryString();

        return view('admin.admins-locais.index', [
            'adminsLocais' => $adminsLocais,
            'filtros' => [
                'q' => $busca,
                'status' => $status,
            ],
            'metricas' => [
                'total' => Usuario::where('perfil_global', 'admin_local')->count(),
                'ativos' => Usuario::where('perfil_global', 'admin_local')->where('ativo', true)->count(),
                'inativos' => Usuario::where('perfil_global', 'admin_local')->where('ativo', false)->count(),
                'primeiro_acesso' => Usuario::where('perfil_global', 'admin_local')->where('primeiro_acesso', true)->count(),
            ],
        ]);
    }

    public function toggle(Usuario $usuario): RedirectResponse
    {
        $this->autorizarNivel7();
        abort_unless($usuario->perfil_global === 'admin_local', 404);

        /** @var \App\Models\Usuario|null $ator */
        $ator = Auth::user();
        $novoStatus = !$usuario->ativo;

        $usuario->forceFill([
            'ativo' => $novoStatus,
        ])->save();

        $this->notificacaoSegurancaService->enviarEventoConta(
            alvo: $usuario,
            evento: $novoStatus ? 'conta_reativada' : 'conta_inativada',
            ator: $ator,
            contexto: [
                'origem' => 'admin_admins_locais_toggle',
                'igreja_id' => $usuario->igreja_id,
                'igreja_nome' => $usuario->igreja?->nome,
                'perfil' => 'admin_local',
            ]
        );

        return back()->with('success', $novoStatus
            ? 'Admin local reativado com sucesso.'
            : 'Admin local inativado com sucesso.');
    }

    public function resetPassword(Usuario $usuario): RedirectResponse
    {
        $this->autorizarNivel7();
        abort_unless($usuario->perfil_global === 'admin_local', 404);

        /** @var \App\Models\Usuario|null $ator */
        $ator = Auth::user();

        $usuario->forceFill([
            'password' => preg_replace('/\D+/', '', (string) $usuario->cpf) ?: (string) $usuario->cpf,
            'primeiro_acesso' => true,
        ])->save();

        $this->notificacaoSegurancaService->enviarEventoConta(
            alvo: $usuario,
            evento: 'reset_senha',
            ator: $ator,
            contexto: [
                'origem' => 'admin_admins_locais_reset',
                'igreja_id' => $usuario->igreja_id,
                'igreja_nome' => $usuario->igreja?->nome,
                'senha_inicial' => 'cpf_sem_pontuacao',
            ]
        );

        return back()->with('success', 'Senha do admin local redefinida com sucesso.');
    }

    private function autorizarNivel7(): void
    {
        /** @var \App\Models\Usuario|null $usuario */
        $usuario = Auth::user();

        abort_unless($usuario && method_exists($usuario, 'nivelGlobal') && $usuario->nivelGlobal() >= 7, 403);
    }
}
