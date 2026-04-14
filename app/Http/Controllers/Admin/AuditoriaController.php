<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditoriaEvento;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuditoriaController extends Controller
{
    public function index(Request $request): View
    {
        $this->autorizarNivel7();

        $evento = trim((string) $request->string('evento'));
        $resultado = trim((string) $request->string('resultado'));
        $busca = trim((string) $request->string('q'));

        $eventos = AuditoriaEvento::query()
            ->with(['ator', 'alvo', 'igreja'])
            ->when($evento !== '', fn ($query) => $query->where('evento', $evento))
            ->when($resultado !== '', fn ($query) => $query->where('resultado', $resultado))
            ->when($busca !== '', function ($query) use ($busca): void {
                $query->where(function ($subquery) use ($busca): void {
                    $subquery->where('protocolo', 'like', '%' . $busca . '%')
                        ->orWhere('ator_nome', 'like', '%' . $busca . '%')
                        ->orWhere('alvo_nome', 'like', '%' . $busca . '%')
                        ->orWhere('alvo_email', 'like', '%' . $busca . '%')
                        ->orWhere('igreja_nome', 'like', '%' . $busca . '%');
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.auditoria.index', [
            'eventos' => $eventos,
            'filtros' => [
                'evento' => $evento,
                'resultado' => $resultado,
                'q' => $busca,
            ],
            'eventosDisponiveis' => [
                'reset_senha' => 'Senha redefinida',
                'conta_inativada' => 'Conta inativada',
                'conta_reativada' => 'Conta reativada',
                'troca_nivel_global' => 'Nivel global alterado',
                'papel_local_concedido' => 'Papel local concedido',
                'papel_local_revogado' => 'Papel local revogado',
            ],
            'resultadosDisponiveis' => [
                'registrado' => 'Registrado',
                'email_enviado' => 'Email enviado',
                'email_falhou' => 'Falha no email',
            ],
            'metricas' => [
                'total' => AuditoriaEvento::count(),
                'hoje' => AuditoriaEvento::whereDate('created_at', now('America/Cuiaba')->toDateString())->count(),
                'email_enviado' => AuditoriaEvento::where('resultado', 'email_enviado')->count(),
                'email_falhou' => AuditoriaEvento::where('resultado', 'email_falhou')->count(),
            ],
        ]);
    }

    public function show(AuditoriaEvento $auditoria): View
    {
        $this->autorizarNivel7();

        $auditoria->load(['ator', 'alvo', 'igreja']);

        return view('admin.auditoria.show', [
            'auditoria' => $auditoria,
            'contexto' => is_array($auditoria->contexto) ? $auditoria->contexto : [],
        ]);
    }

    private function autorizarNivel7(): void
    {
        /** @var Usuario|null $usuario */
        $usuario = Auth::user();

        abort_unless($usuario && method_exists($usuario, 'nivelGlobal') && $usuario->nivelGlobal() >= 7, 403);
    }
}
