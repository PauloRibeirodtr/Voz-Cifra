<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\ColecaoEstudo;
use App\Models\Usuario;
use App\Models\VersaoMusical;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ColecaoEstudoController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'verified_custom', 'role:member']);
    }

    public function index(): View
    {
        $usuario = $this->obterUsuario();

        $colecoes = $usuario->colecoesEstudo()
            ->withCount('itens')
            ->with([
                'itens' => fn ($query) => $query
                    ->with(['musica', 'versaoMusical'])
                    ->latest()
                    ->take(4),
            ])
            ->latest()
            ->get();

        return view('member.colecoes.index', [
            'colecoes' => $colecoes,
            'usuario' => $usuario,
            'igreja' => $usuario->igreja,
        ]);
    }

    public function show(ColecaoEstudo $colecao): View
    {
        $usuario = $this->obterUsuario();
        abort_unless((int) $colecao->usuario_id === (int) $usuario->id, 403);

        $colecao->load([
            'itens' => fn ($query) => $query
                ->with(['musica', 'versaoMusical'])
                ->latest(),
        ]);

        return view('member.colecoes.show', [
            'colecao' => $colecao,
            'usuario' => $usuario,
            'igreja' => $usuario->igreja,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $usuario = $this->obterUsuario();

        $dados = $request->validate([
            'nome' => ['required', 'string', 'max:120'],
            'musica_id' => ['nullable', 'integer', Rule::exists('musicas', 'id')],
            'versao_musical_id' => ['nullable', 'integer', Rule::exists('versoes_musicais', 'id')],
        ]);

        if (!empty($dados['versao_musical_id']) && !empty($dados['musica_id'])) {
            if (!$this->versaoPertenceAMusica((int) $dados['versao_musical_id'], (int) $dados['musica_id'])) {
                return back()->withErrors(['versao_musical_id' => 'A versao escolhida nao pertence a musica informada.']);
            }
        }

        $colecao = $usuario->colecoesEstudo()->create([
            'nome' => trim($dados['nome']),
        ]);

        if (!empty($dados['versao_musical_id']) && !empty($dados['musica_id'])) {
            $colecao->itens()->firstOrCreate([
                'musica_id' => $dados['musica_id'],
            ], [
                'versao_musical_id' => $dados['versao_musical_id'],
            ]);
        }

        return back()->with('success', 'Playlist criada com sucesso.');
    }

    public function adicionarItem(Request $request, ColecaoEstudo $colecao): RedirectResponse
    {
        $usuario = $this->obterUsuario();
        abort_unless((int) $colecao->usuario_id === (int) $usuario->id, 403);

        $dados = $request->validate([
            'musica_id' => ['required', 'integer', Rule::exists('musicas', 'id')],
            'versao_musical_id' => ['required', 'integer', Rule::exists('versoes_musicais', 'id')],
        ]);

        if (!$this->versaoPertenceAMusica((int) $dados['versao_musical_id'], (int) $dados['musica_id'])) {
            return back()->withErrors(['versao_musical_id' => 'A versao escolhida nao pertence a musica informada.']);
        }

        $itemExistente = $colecao->itens()
            ->where('musica_id', $dados['musica_id'])
            ->first();

        if ($itemExistente) {
            return back()->with('status', 'Esta musica ja esta nesta playlist.');
        }

        $colecao->itens()->create([
            'musica_id' => $dados['musica_id'],
            'versao_musical_id' => $dados['versao_musical_id'],
        ]);

        return back()->with('success', 'Musica adicionada a playlist.');
    }

    public function removerItem(ColecaoEstudo $colecao, int $item): RedirectResponse
    {
        $usuario = $this->obterUsuario();
        abort_unless((int) $colecao->usuario_id === (int) $usuario->id, 403);

        $colecao->itens()->whereKey($item)->delete();

        return back()->with('success', 'Item removido da playlist.');
    }

    private function obterUsuario(): Usuario
    {
        /** @var \App\Models\Usuario $usuario */
        $usuario = Auth::user();

        abort_unless($usuario && $usuario->ehMembro(), 403);

        return $usuario;
    }

    private function versaoPertenceAMusica(int $versaoMusicalId, int $musicaId): bool
    {
        return VersaoMusical::query()
            ->whereKey($versaoMusicalId)
            ->where('musica_id', $musicaId)
            ->where('ativo', true)
            ->exists();
    }
}
