<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Missa;
use App\Models\Musica;
use App\Models\Usuario;
use App\Models\VersaoMusical;
use App\Services\TranspositorCifrasService;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class BibliotecaMusicalController extends Controller
{
    public function __construct(
        private readonly TranspositorCifrasService $transpositorCifrasService
    ) {
        $this->middleware(['auth', 'verified_custom', 'role:member']);
    }

    public function repertorio(): View
    {
        $usuario = $this->obterUsuario();
        $igreja = $usuario->igreja;
        $hoje = CarbonImmutable::now('America/Cuiaba')->toDateString();

        $missa = Missa::query()
            ->with([
                'tempoLiturgico',
                'missaMusicas' => fn ($query) => $query
                    ->with(['musica', 'versaoMusical', 'momentoLiturgico'])
                    ->orderBy('ordem'),
            ])
            ->where('igreja_id', $igreja?->id)
            ->where(function ($query) use ($hoje) {
                $query->where('ativo', true)
                    ->orWhereDate('data_missa', '>=', $hoje);
            })
            ->orderByRaw('case when ativo then 0 else 1 end')
            ->orderBy('data_missa')
            ->orderBy('hora_inicio')
            ->first();

        return view('member.repertorio', [
            'usuario' => $usuario,
            'igreja' => $igreja,
            'missa' => $missa,
        ]);
    }

    public function musicas(Request $request): View
    {
        $consulta = Musica::query()
            ->with([
                'tempoLiturgico',
                'momentoLiturgico',
                'versoesMusicais' => fn ($query) => $query->where('ativo', true)->orderBy('titulo'),
            ])
            ->where('ativo', true)
            ->whereHas('versoesMusicais', fn ($query) => $query->where('ativo', true))
            ->orderBy('titulo');

        if ($request->filled('busca')) {
            $termo = trim((string) $request->input('busca'));

            $consulta->where(function ($query) use ($termo): void {
                $query->where('titulo', 'like', "%{$termo}%")
                    ->orWhere('artista', 'like', "%{$termo}%")
                    ->orWhere('letra', 'like', "%{$termo}%");
            });
        }

        return view('member.musicas.index', [
            'usuario' => $this->obterUsuario(),
            'igreja' => $this->obterUsuario()->igreja,
            'musicas' => $consulta->paginate(12)->withQueryString(),
            'busca' => (string) $request->input('busca', ''),
        ]);
    }

    public function versao(Musica $musica, VersaoMusical $versaoMusical): View
    {
        abort_unless((int) $versaoMusical->musica_id === (int) $musica->id, 404);
        abort_unless($musica->ativo && $versaoMusical->ativo, 404);

        $usuario = $this->obterUsuario();
        $igreja = $usuario->igreja;

        $missaAtiva = Missa::query()
            ->with(['missaMusicas' => fn ($query) => $query->where('versao_musical_id', $versaoMusical->id)])
            ->where('igreja_id', $igreja?->id)
            ->where('ativo', true)
            ->first();

        $itemMissa = $missaAtiva?->missaMusicas?->first();
        $tomOriginal = $versaoMusical->tom_musical;
        $tomExibicao = $itemMissa?->tom_usado ?: $tomOriginal;
        $passos = $this->transpositorCifrasService->calcularPassos($tomOriginal, $tomExibicao);

        return view('member.versoes.show', [
            'usuario' => $usuario,
            'igreja' => $igreja,
            'musica' => $musica,
            'versaoMusical' => $versaoMusical,
            'missaAtiva' => $missaAtiva,
            'itemMissa' => $itemMissa,
            'textoCifraExibicao' => $this->transpositorCifrasService->transporTextoCifrado($versaoMusical->letra_com_cifras, $passos),
            'tomOriginal' => $tomOriginal,
            'tomExibicao' => $this->transpositorCifrasService->transporTomExibicao($tomOriginal, $tomExibicao),
        ]);
    }

    private function obterUsuario(): Usuario
    {
        /** @var \App\Models\Usuario $usuario */
        $usuario = Auth::user();

        abort_unless($usuario && $usuario->ehMembro(), 403);

        return $usuario;
    }
}
