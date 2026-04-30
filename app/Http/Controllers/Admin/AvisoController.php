<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PapelIgreja;
use App\Http\Controllers\Controller;
use App\Models\Igreja;
use App\Models\Usuario;
use App\Services\AuditoriaOperacionalService;
use App\Services\NotificacaoSistemaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AvisoController extends Controller
{
    public function __construct(
        private readonly NotificacaoSistemaService $notificacaoSistemaService,
        private readonly AuditoriaOperacionalService $auditoriaOperacionalService
    ) {
    }

    public function create(): View
    {
        return view('admin.avisos.create', [
            'igrejas' => Igreja::query()->where('ativo', true)->orderBy('nome')->get(['id', 'nome', 'cidade', 'estado']),
            'usuarios' => Usuario::query()->where('ativo', true)->orderBy('nome')->get(['id', 'nome', 'email']),
            'papeis' => PapelIgreja::cases(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $dados = $request->validate([
            'escopo' => ['required', Rule::in(['todos', 'igreja', 'papel', 'usuario'])],
            'titulo' => ['required', 'string', 'max:120'],
            'mensagem' => ['required', 'string', 'max:3000'],
            'igreja_id' => ['nullable', 'required_if:escopo,igreja', 'exists:igrejas,id'],
            'papel' => ['nullable', 'required_if:escopo,papel', Rule::in(PapelIgreja::values())],
            'usuario_id' => ['nullable', 'required_if:escopo,usuario', 'exists:usuarios,id'],
        ]);

        /** @var \App\Models\Usuario $ator */
        $ator = Auth::user();
        $destinatarios = $this->resolverDestinatarios($dados);

        if ($destinatarios->isEmpty()) {
            return back()
                ->withInput()
                ->withErrors(['destinatarios' => 'Nenhum usuario ativo encontrado para o filtro escolhido.']);
        }

        $contexto = [
            'origem' => 'admin_avisos_store',
            'escopo' => $dados['escopo'],
            'titulo' => $dados['titulo'],
            'mensagem' => $dados['mensagem'],
            'quantidade' => $destinatarios->count(),
        ];

        if (!empty($dados['igreja_id'])) {
            $igreja = Igreja::query()->find((int) $dados['igreja_id']);
            $contexto['igreja_id'] = $igreja?->id;
            $contexto['igreja_nome'] = $igreja?->nome;
        }

        if (!empty($dados['papel'])) {
            $contexto['papel'] = PapelIgreja::fromValue($dados['papel'])->label();
        }

        $this->notificacaoSistemaService->enviarParaUsuarios(
            $destinatarios,
            'aviso_admin',
            $ator,
            $contexto
        );

        $this->auditoriaOperacionalService->registrar(
            evento: 'aviso_admin_enviado',
            ator: $ator,
            igreja: $contexto['igreja_id'] ?? null,
            contexto: $contexto
        );

        return redirect()
            ->route('admin.avisos.create')
            ->with('success', 'Aviso enviado para ' . $destinatarios->count() . ' usuario(s).');
    }

    /**
     * @param array<string, mixed> $dados
     * @return Collection<int, Usuario>
     */
    private function resolverDestinatarios(array $dados): Collection
    {
        $query = Usuario::query()
            ->where('ativo', true)
            ->whereNotNull('email')
            ->where('email', '!=', '');

        return match ($dados['escopo']) {
            'igreja' => $query
                ->whereHas('vinculosIgreja', fn ($vinculo) => $vinculo
                    ->where('igreja_id', (int) $dados['igreja_id'])
                    ->where('ativo', true))
                ->orderBy('nome')
                ->get(),
            'papel' => $query
                ->whereHas('papeisAtivosPorIgreja', fn ($papel) => $papel->where('papel', (string) $dados['papel']))
                ->orderBy('nome')
                ->get(),
            'usuario' => $query
                ->whereKey((int) $dados['usuario_id'])
                ->get(),
            default => $query->orderBy('nome')->get(),
        };
    }
}
