<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Acorde;
use App\Models\Missa;
use App\Models\Musica;
use App\Models\Usuario;
use App\Models\VersaoMusical;
use App\Services\TranspositorCifrasService;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
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
            'colecoes' => $usuario->colecoesEstudo()->withCount('itens')->latest()->take(4)->get(),
        ]);
    }

    public function musicas(Request $request): View
    {
        $usuario = $this->obterUsuario();
        $igreja = $usuario->igreja;

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

        $musicas = $consulta
            ->paginate(12)
            ->through(function (Musica $musica): Musica {
                $musica->setAttribute(
                    'trecho_letra',
                    Str::of($musica->letra ?? '')->squish()->limit(180)->value()
                );

                return $musica;
            })
            ->withQueryString();

        return view('member.musicas.index', [
            'usuario' => $usuario,
            'igreja' => $igreja,
            'musicas' => $musicas,
            'busca' => (string) $request->input('busca', ''),
            'colecoes' => $usuario->colecoesEstudo()
                ->withCount('itens')
                ->with([
                    'itens' => fn ($query) => $query
                        ->with(['musica', 'versaoMusical'])
                        ->latest()
                        ->take(3),
                ])
                ->latest()
                ->take(6)
                ->get(),
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
        $textoCifraExibicao = $this->transpositorCifrasService->transporTextoCifrado($versaoMusical->letra_com_cifras, $passos);

        return view('member.versoes.show', [
            'usuario' => $usuario,
            'igreja' => $igreja,
            'musica' => $musica,
            'versaoMusical' => $versaoMusical,
            'missaAtiva' => $missaAtiva,
            'itemMissa' => $itemMissa,
            'textoCifraExibicao' => $textoCifraExibicao,
            'tomOriginal' => $tomOriginal,
            'tomExibicao' => $this->transpositorCifrasService->transporTomExibicao($tomOriginal, $tomExibicao),
            'bibliotecaAcordes' => Acorde::query()
                ->where('ativo', true)
                ->orderBy('nome')
                ->orderBy('descricao')
                ->get()
                ->map(fn (Acorde $acorde) => [
                    'id' => $acorde->id,
                    'nome' => $acorde->nome,
                    'descricao' => $acorde->descricao,
                    'shape' => $acorde->dados_diagrama,
                ])
                ->values(),
            'acordesDaVersao' => $this->extrairAcordes($textoCifraExibicao),
            'colecoes' => $usuario->colecoesEstudo()
                ->withCount('itens')
                ->latest()
                ->get(),
            'colecaoIdsComVersao' => $usuario->colecoesEstudo()
                ->whereHas('itens', fn ($query) => $query->where('versao_musical_id', $versaoMusical->id))
                ->pluck('id'),
        ]);
    }

    private function extrairAcordes(string $texto): array
    {
        preg_match_all('/\[([^\[\]\r\n]+)\]/', $texto, $matches);

        return collect($matches[1] ?? [])
            ->map(fn ($acorde) => trim((string) $acorde))
            ->filter(fn ($acorde) => $acorde !== '')
            ->unique()
            ->values()
            ->all();
    }

    private function obterUsuario(): Usuario
    {
        /** @var \App\Models\Usuario $usuario */
        $usuario = Auth::user();

        abort_unless($usuario && $usuario->ehMembro(), 403);

        return $usuario;
    }
}
