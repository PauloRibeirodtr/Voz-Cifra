<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditoriaEvento;
use App\Models\Igreja;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuditoriaController extends Controller
{
    public function index(Request $request): View
    {
        $this->autorizarAdminMaster();

        $evento = trim((string) $request->string('evento'));
        $categoria = trim((string) $request->string('categoria'));
        $resultado = trim((string) $request->string('resultado'));
        $igrejaId = (int) $request->integer('igreja_id');
        $busca = trim((string) $request->string('q'));

        $eventos = AuditoriaEvento::query()
            ->with(['ator', 'alvo', 'igreja'])
            ->when($categoria !== '', fn ($query) => $query->where('categoria', $categoria))
            ->when($evento !== '', fn ($query) => $query->where('evento', $evento))
            ->when($resultado !== '', fn ($query) => $query->where('resultado', $resultado))
            ->when($igrejaId > 0, fn ($query) => $query->where('igreja_id', $igrejaId))
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
                'categoria' => $categoria,
                'evento' => $evento,
                'resultado' => $resultado,
                'igreja_id' => $igrejaId > 0 ? $igrejaId : '',
                'q' => $busca,
            ],
            'eventosDisponiveis' => [
                'igreja_criada' => 'Igreja criada',
                'igreja_editada' => 'Igreja editada',
                'admin_local_vinculado' => 'Admin local vinculado',
                'coordenador_vinculado' => 'Coordenador vinculado',
                'musico_vinculado' => 'Musico vinculado',
                'musica_criada' => 'Musica criada',
                'musica_editada' => 'Musica editada',
                'musica_inativada' => 'Musica inativada',
                'missa_criada' => 'Missa criada',
                'missa_editada' => 'Missa editada',
                'repertorio_item_adicionado' => 'Item adicionado ao repertorio',
                'repertorio_item_atualizado' => 'Item do repertorio atualizado',
                'repertorio_item_removido' => 'Item removido do repertorio',
                'versao_musical_criada' => 'Versao musical criada',
                'versao_musical_editada' => 'Versao musical editada',
                'versao_musical_inativada' => 'Versao musical inativada',
                'reset_senha' => 'Senha redefinida',
                'conta_inativada' => 'Conta inativada',
                'conta_reativada' => 'Conta reativada',
                'troca_nivel_global' => 'Acesso global alterado',
                'papel_local_concedido' => 'Papel local concedido',
                'papel_local_revogado' => 'Papel local revogado',
            ],
            'categoriasDisponiveis' => [
                'operacao' => 'Operacao',
                'seguranca' => 'Seguranca',
                'sistema' => 'Sistema',
            ],
            'resultadosDisponiveis' => [
                'registrado' => 'Registrado',
                'email_enviado' => 'Email enviado',
                'email_falhou' => 'Falha no email',
            ],
            'igrejas' => Igreja::query()->orderBy('nome')->get(['id', 'nome']),
            'metricas' => [
                'total' => AuditoriaEvento::count(),
                'hoje' => AuditoriaEvento::whereDate('created_at', now('America/Cuiaba')->toDateString())->count(),
                'operacoes' => AuditoriaEvento::where('categoria', 'operacao')->count(),
                'email_enviado' => AuditoriaEvento::where('resultado', 'email_enviado')->count(),
                'email_falhou' => AuditoriaEvento::where('resultado', 'email_falhou')->count(),
            ],
        ]);
    }

    public function show(AuditoriaEvento $auditoria): View
    {
        $this->autorizarAdminMaster();

        $auditoria->load(['ator', 'alvo', 'igreja']);

        return view('admin.auditoria.show', [
            'auditoria' => $auditoria,
            'contexto' => is_array($auditoria->contexto) ? $auditoria->contexto : [],
        ]);
    }

    private function autorizarAdminMaster(): void
    {
        /** @var Usuario|null $usuario */
        $usuario = Auth::user();

        abort_unless($usuario && $usuario->ehAdminMaster(), 403);
    }
}
