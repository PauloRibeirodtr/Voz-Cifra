<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PapelIgreja;
use App\Http\Controllers\Controller;
use App\Models\Usuario;
use App\Services\GestaoUsuariosIgrejaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AdminLocalController extends Controller
{
    public function __construct(
        private readonly GestaoUsuariosIgrejaService $gestaoUsuariosIgrejaService,
    ) {
    }

    public function index(Request $request): View
    {
        $this->autorizarAdminMaster();

        $busca = trim((string) $request->string('q'));
        $status = trim((string) $request->string('status'));

        $adminsLocais = Usuario::query()
            ->with('igreja')
            ->whereHas('papeisAtivosPorIgreja', fn ($subQuery) => $subQuery->where('papel', PapelIgreja::ADMIN_LOCAL->value))
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
                'total' => Usuario::query()
                    ->whereHas('papeisAtivosPorIgreja', fn ($subQuery) => $subQuery->where('papel', PapelIgreja::ADMIN_LOCAL->value))
                    ->count(),
                'ativos' => Usuario::query()
                    ->where('ativo', true)
                    ->whereHas('papeisAtivosPorIgreja', fn ($subQuery) => $subQuery->where('papel', PapelIgreja::ADMIN_LOCAL->value))
                    ->count(),
                'inativos' => Usuario::query()
                    ->where('ativo', false)
                    ->whereHas('papeisAtivosPorIgreja', fn ($subQuery) => $subQuery->where('papel', PapelIgreja::ADMIN_LOCAL->value))
                    ->count(),
                'primeiro_acesso' => Usuario::query()
                    ->where('primeiro_acesso', true)
                    ->whereHas('papeisAtivosPorIgreja', fn ($subQuery) => $subQuery->where('papel', PapelIgreja::ADMIN_LOCAL->value))
                    ->count(),
            ],
        ]);
    }

    public function toggle(Usuario $usuario): RedirectResponse
    {
        $this->autorizarAdminMaster();
        abort_unless($this->ehAdminLocal($usuario), 404);

        /** @var \App\Models\Usuario|null $ator */
        $ator = Auth::user();
        $usuario = $this->gestaoUsuariosIgrejaService->alterarStatusConta(
            usuario: $usuario,
            ativo: !$usuario->ativo,
            ator: $ator,
            contexto: [
                'origem' => 'admin_admins_locais_toggle',
                'igreja_id' => $usuario->igrejaAtiva()?->id ?? $usuario->igreja_id,
                'igreja_nome' => $usuario->igrejaAtiva()?->nome ?? $usuario->igreja?->nome,
                'perfil' => 'admin_local',
            ]
        );

        return back()->with('success', $usuario->ativo
            ? 'Admin local reativado com sucesso.'
            : 'Admin local inativado com sucesso.');
    }

    public function resetPassword(Usuario $usuario): RedirectResponse
    {
        $this->autorizarAdminMaster();
        abort_unless($this->ehAdminLocal($usuario), 404);

        /** @var \App\Models\Usuario|null $ator */
        $ator = Auth::user();

        $this->gestaoUsuariosIgrejaService->enviarLinkDefinicaoSenha(
            usuario: $usuario,
            ator: $ator,
            contexto: [
                'origem' => 'admin_admins_locais_reset',
                'igreja_id' => $usuario->igrejaAtiva()?->id ?? $usuario->igreja_id,
                'igreja_nome' => $usuario->igrejaAtiva()?->nome ?? $usuario->igreja?->nome,
            ]
        );

        return back()->with('success', 'Link de definicao de senha enviado ao admin local. Ele expira em 60 minutos.');
    }

    private function autorizarAdminMaster(): void
    {
        /** @var \App\Models\Usuario|null $usuario */
        $usuario = Auth::user();

        abort_unless($usuario && $usuario->ehAdminMaster(), 403);
    }

    private function ehAdminLocal(Usuario $usuario): bool
    {
        return $usuario->temPapelNaIgreja(PapelIgreja::ADMIN_LOCAL);
    }
}
