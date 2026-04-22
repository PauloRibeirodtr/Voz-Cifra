<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Acorde;
use App\Models\Musica;
use App\Models\Usuario;
use App\Models\VersaoMusical;
use App\Services\FolhaVersaoMusicalService;
use App\Services\RepertorioMusicoService;
use App\Services\TranspositorCifrasService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class BibliotecaMusicalController extends Controller
{
    public function __construct(
        private readonly TranspositorCifrasService $transpositorCifrasService,
        private readonly FolhaVersaoMusicalService $folhaVersaoMusicalService,
        private readonly RepertorioMusicoService $repertorioMusicoService
    ) {
        $this->middleware(['auth', 'verified_custom', 'role:member']);
    }

    public function repertorio(): View
    {
        $usuario = $this->obterUsuario();
        $igreja = $usuario->igrejaAtiva() ?? $usuario->igreja;
        $missa = $this->repertorioMusicoService->obterMissaDisponivelParaUsuario($usuario);

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
        $igreja = $usuario->igrejaAtiva() ?? $usuario->igreja;

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
        $usuario = $this->obterUsuario();
        $igreja = $usuario->igrejaAtiva() ?? $usuario->igreja;
        [
            'missaAtiva' => $missaAtiva,
            'itemMissa' => $itemMissa,
            'tomOriginal' => $tomOriginal,
            'tomExibicao' => $tomExibicao,
            'textoCifraExibicao' => $textoCifraExibicao,
        ] = $this->montarContextoVersao($musica, $versaoMusical);

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

    public function imprimirVersao(Musica $musica, VersaoMusical $versaoMusical): View
    {
        $usuario = $this->obterUsuario();
        $igreja = $usuario->igrejaAtiva() ?? $usuario->igreja;
        $contexto = $this->montarContextoVersao($musica, $versaoMusical);

        $folha = $this->folhaVersaoMusicalService->montar(
            $versaoMusical,
            $contexto['textoCifraExibicao'],
            $contexto['tomExibicao'],
            [
                'Musica' => $musica->titulo,
                'Contexto' => $contexto['itemMissa'] ? 'Versao usada na missa ativa' : 'Biblioteca musical',
                'Igreja' => $igreja?->nome,
            ]
        );

        return view('shared.versao-print', [
            'folha' => $folha,
            'etiquetaFolha' => 'Folha do musico',
            'pdfUrl' => route('member.versoes.pdf', [$musica, $versaoMusical]),
            'backUrl' => route('member.versoes.show', [$musica, $versaoMusical]),
            'pageTitle' => ($versaoMusical->titulo ?: $musica->titulo) . ' | Impressao',
        ]);
    }

    public function pdfVersao(Musica $musica, VersaoMusical $versaoMusical)
    {
        $usuario = $this->obterUsuario();
        $igreja = $usuario->igrejaAtiva() ?? $usuario->igreja;
        $contexto = $this->montarContextoVersao($musica, $versaoMusical);

        $folha = $this->folhaVersaoMusicalService->montar(
            $versaoMusical,
            $contexto['textoCifraExibicao'],
            $contexto['tomExibicao'],
            [
                'Musica' => $musica->titulo,
                'Contexto' => $contexto['itemMissa'] ? 'Versao usada na missa ativa' : 'Biblioteca musical',
                'Igreja' => $igreja?->nome,
            ]
        );

        return Pdf::loadView('shared.versao-pdf', [
            'folha' => $folha,
            'etiquetaFolha' => 'Folha do musico',
            'pageTitle' => ($versaoMusical->titulo ?: $musica->titulo) . ' | PDF',
        ])
            ->setPaper('a4', 'portrait')
            ->download('musica-' . Str::slug($musica->titulo ?: 'versao') . '-versao-' . $versaoMusical->id . '.pdf');
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

    private function montarContextoVersao(Musica $musica, VersaoMusical $versaoMusical): array
    {
        abort_unless((int) $versaoMusical->musica_id === (int) $musica->id, 404);
        abort_unless($musica->ativo && $versaoMusical->ativo, 404);

        $usuario = $this->obterUsuario();
        $missaAtiva = $this->repertorioMusicoService->obterMissaComVersaoParaUsuario($usuario, $versaoMusical);

        $itemMissa = $missaAtiva?->missaMusicas?->first();
        $tomOriginal = $versaoMusical->tom_musical;
        $tomExibicao = $itemMissa?->tom_usado ?: $tomOriginal;
        $passos = $this->transpositorCifrasService->calcularPassos($tomOriginal, $tomExibicao);
        $textoCifraExibicao = $this->transpositorCifrasService->transporTextoCifrado($versaoMusical->letra_com_cifras, $passos);

        return [
            'missaAtiva' => $missaAtiva,
            'itemMissa' => $itemMissa,
            'tomOriginal' => $tomOriginal,
            'tomExibicao' => $this->transpositorCifrasService->transporTomExibicao($tomOriginal, $tomExibicao),
            'textoCifraExibicao' => $textoCifraExibicao,
        ];
    }
}
