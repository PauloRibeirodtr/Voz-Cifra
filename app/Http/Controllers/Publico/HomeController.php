<?php

namespace App\Http\Controllers\Publico;

use App\Http\Controllers\Controller;
use App\Models\Igreja;
use App\Models\Missa;
use App\Models\TempoLiturgico;
use App\Models\Usuario;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(Request $request): View|\Illuminate\Http\RedirectResponse
    {
        if (Auth::check()) {
            /** @var Usuario|null $usuario */
            $usuario = Auth::user();

            if ($usuario?->primeiro_acesso && method_exists($usuario, 'rotaDestinoPrimeiroAcesso')) {
                $rotaPrimeiroAcesso = $usuario->rotaDestinoPrimeiroAcesso();

                if ($rotaPrimeiroAcesso !== null) {
                    return redirect()
                        ->route($rotaPrimeiroAcesso)
                        ->with('status', $usuario->mensagemPrimeiroAcesso());
                }
            }

            if (method_exists($usuario, 'rotaDestinoAposLogin')) {
                $rotaDestino = $usuario->rotaDestinoAposLogin();

                if ($rotaDestino !== null) {
                    return redirect()->route($rotaDestino);
                }
            }

            return redirect()->route('login');
        }

        $timezone = 'America/Cuiaba';
        $agora = CarbonImmutable::now($timezone);

        $igrejas = Igreja::query()
            ->where('ativo', true)
            ->orderBy('nome')
            ->get(['id', 'nome', 'slug', 'cidade', 'estado', 'bairro', 'endereco', 'numero', 'telefone_secretaria', 'imagem_path']);

        $temposLiturgicos = TempoLiturgico::query()
            ->where('ativo', true)
            ->orderBy('nome')
            ->get(['id', 'nome']);

        $tiposCelebracao = Missa::query()
            ->select('titulo')
            ->where('ativo', true)
            ->where('publica_para_fieis', true)
            ->whereNotNull('titulo')
            ->where('titulo', '!=', '')
            ->distinct()
            ->orderBy('titulo')
            ->pluck('titulo');

        $baseQuery = Missa::query()
            ->with(['igreja:id,nome,slug,cidade,estado', 'tempoLiturgico:id,nome'])
            ->withCount('missaMusicas')
            ->where('ativo', true)
            ->where('publica_para_fieis', true)
            ->whereHas('igreja', fn ($query) => $query->where('ativo', true));

        $missasVisiveis = (clone $baseQuery)
            ->when($request->filled('data'), fn ($query) => $query->whereDate('data_missa', $request->input('data')))
            ->when($request->filled('igreja'), fn ($query) => $query->where('igreja_id', (int) $request->input('igreja')))
            ->when($request->filled('tempo_liturgico'), fn ($query) => $query->where('tempo_liturgico_id', (int) $request->input('tempo_liturgico')))
            ->when($request->filled('tipo'), fn ($query) => $query->where('titulo', $request->input('tipo')))
            ->orderByDesc('data_missa')
            ->orderByDesc('hora_inicio')
            ->limit(24)
            ->get()
            ->map(fn (Missa $missa) => $this->mapearMissaPublica($missa, $agora, $timezone))
            ->pipe(fn (Collection $missas) => $this->ordenarMissas($missas))
            ->values();

        $missaEmDestaque = $missasVisiveis->first();
        $outrasMissas = $missasVisiveis->slice(1)->take(6)->values();

        $igrejasDestaque = $igrejas
            ->map(function (Igreja $igreja) use ($agora, $timezone): array {
                $proximaMissa = Missa::query()
                    ->where('ativo', true)
                    ->where('publica_para_fieis', true)
                    ->where('igreja_id', $igreja->id)
                    ->orderBy('data_missa')
                    ->orderBy('hora_inicio')
                    ->get()
                    ->map(fn (Missa $missa) => $this->mapearMissaPublica($missa, $agora, $timezone))
                    ->first(fn (array $missa) => in_array($missa['status']['slug'], ['acontecendo_agora', 'preparada', 'publicada'], true));

                return [
                    'nome' => $igreja->nome,
                    'slug' => $igreja->slug,
                    'imagem_url' => $igreja->imagemUrl(),
                    'tem_imagem_personalizada' => $igreja->temImagemPersonalizada(),
                    'localidade' => trim(($igreja->cidade ?? '') . ' - ' . ($igreja->estado ?? ''), ' -'),
                    'cidade' => $igreja->cidade,
                    'estado' => $igreja->estado,
                    'bairro' => $igreja->bairro,
                    'endereco' => trim(($igreja->endereco ?? '') . ($igreja->numero ? ', ' . $igreja->numero : ''), ' ,'),
                    'telefone_secretaria' => $igreja->telefone_secretaria,
                    'proxima_missa' => $proximaMissa['titulo'] ?? 'Sem missa publicada no momento',
                    'proxima_data' => $proximaMissa['data_formatada'] ?? 'Aguardando nova celebracao',
                    'proxima_horario' => $proximaMissa['horario_curto'] ?? null,
                    'proxima_status' => $proximaMissa['status']['label'] ?? null,
                    'proxima_url' => $proximaMissa['url'] ?? ($igreja->slug ? route('igrejas.public.show', ['slug' => $igreja->slug]) : route('login')),
                ];
            })
            ->values();

        return view('publico.home', [
            'heroImage' => asset('images/missa1.jpg'),
            'missaEmDestaque' => $missaEmDestaque,
            'missasRecentes' => $outrasMissas,
            'igrejasDestaque' => $igrejasDestaque,
            'igrejas' => $igrejas,
            'temposLiturgicos' => $temposLiturgicos,
            'tiposCelebracao' => $tiposCelebracao,
            'filtros' => [
                'data' => (string) $request->input('data', ''),
                'igreja' => (string) $request->input('igreja', ''),
                'tempo_liturgico' => (string) $request->input('tempo_liturgico', ''),
                'tipo' => (string) $request->input('tipo', ''),
            ],
        ]);
    }

    public function desenvolvedores(): View
    {
        return view('publico.desenvolvedores', [
            'equipeProjeto' => $this->equipeProjeto(),
            'logoInstituicao' => asset('instituicao/ifms.png'),
        ]);
    }

    private function equipeProjeto(): Collection
    {
        return collect([
            [
                'nome' => 'Roberth Arnaldo Loogam Souza da Silva',
                'foto' => asset('team/roberth.png'),
                'papel' => 'Desenvolvimento principal',
                'descricao' => 'Responsavel pela programacao backend, frontend e integracao principal do sistema.',
                'github' => 'https://github.com/roberth-silva-lab',
            ],
            [
                'nome' => 'Luis Gaudencio',
                'foto' => asset('team/gaudencio.png'),
                'papel' => 'Requisitos e banco',
                'descricao' => 'Responsavel pelo levantamento de requisitos, diagramas de caso de uso, documentacao e modelagem de banco de dados.',
                'github' => 'https://github.com/Gaudencios',
            ],
            [
                'nome' => 'Julio Cesar',
                'foto' => asset('team/julio.png'),
                'papel' => 'Analise e documentacao',
                'descricao' => 'Atua no levantamento de requisitos, organizacao funcional e apoio na documentacao do projeto.',
                'github' => 'https://github.com/Fungus-48',
            ],
            [
                'nome' => 'Vitor',
                'foto' => asset('team/vitor.png'),
                'papel' => 'Apoio em frontend',
                'descricao' => 'Colabora com ajustes visuais, refinamentos de interface e apoio em entregas de frontend.',
                'github' => 'https://github.com/vitorgomesleite-creator',
            ],
        ]);
    }

    private function ordenarMissas(Collection $missas): Collection
    {
        $ordemStatus = [
            'acontecendo_agora' => 0,
            'preparada' => 1,
            'publicada' => 2,
            'rascunho' => 3,
            'encerrada' => 4,
            'historico' => 5,
        ];

        return $missas->sort(function (array $a, array $b) use ($ordemStatus): int {
            $pesoA = $ordemStatus[$a['status']['slug']] ?? 99;
            $pesoB = $ordemStatus[$b['status']['slug']] ?? 99;

            if ($pesoA !== $pesoB) {
                return $pesoA <=> $pesoB;
            }

            return strcmp($b['inicio_iso'], $a['inicio_iso']);
        });
    }

    private function mapearMissaPublica(Missa $missa, CarbonImmutable $agora, string $timezone): array
    {
        $inicio = $missa->dataHoraInicio($timezone);
        $fim = $missa->dataHoraFim($timezone);

        $status = $this->resolverStatus($missa, $inicio, $fim, $agora);

        return [
            'id' => $missa->id,
            'titulo' => $missa->titulo,
            'igreja' => $missa->igreja?->nome ?? 'Igreja nao informada',
            'igreja_slug' => $missa->igreja?->slug,
            'igreja_localidade' => trim(($missa->igreja?->cidade ?? '') . ' - ' . ($missa->igreja?->estado ?? ''), ' -'),
            'tempo_liturgico' => $missa->tempoLiturgico?->nome ?? 'Tempo liturgico nao definido',
            'data_formatada' => $inicio->isoFormat('DD [de] MMMM [de] YYYY'),
            'data_curta' => $inicio->format('d/m/Y'),
            'horario' => $inicio->format('H:i') . ' - ' . $fim->format('H:i'),
            'horario_curto' => $inicio->format('H:i'),
            'inicio_iso' => $inicio->toIso8601String(),
            'status' => $status,
            'tipo' => $missa->titulo,
            'resumo' => $this->montarResumoMissa($status['slug'], $inicio, $missa),
            'url' => $missa->igreja?->slug
                ? route('igrejas.public.show', ['slug' => $missa->igreja->slug, 'celebracao' => $missa->id]) . '#celebracao-publica'
                : route('login'),
        ];
    }

    private function montarResumoMissa(string $status, CarbonImmutable $inicio, Missa $missa): string
    {
        return match ($status) {
            'acontecendo_agora' => 'Celebracao em andamento neste momento.',
            'preparada' => 'Repertorio organizado para a celebracao de ' . $inicio->format('d/m') . '.',
            'publicada' => 'Celebracao publicada para consulta e compartilhamento.',
            'rascunho' => 'Celebracao ainda em preparacao pela equipe local.',
            'encerrada' => 'Celebracao encerrada hoje e mantida para consulta.',
            default => 'Celebracao anterior registrada no historico da comunidade.',
        };
    }

    private function resolverStatus(Missa $missa, CarbonImmutable $inicio, CarbonImmutable $fim, CarbonImmutable $agora): array
    {
        if ($inicio->lessThanOrEqualTo($agora) && $fim->greaterThanOrEqualTo($agora) && $missa->ativo) {
            return $this->statusBadge('Acontecendo agora', 'acontecendo_agora');
        }

        if ($inicio->greaterThan($agora) && $missa->ativo && $missa->missa_musicas_count > 0) {
            return $this->statusBadge('Preparada', 'preparada');
        }

        if ($inicio->greaterThan($agora) && $missa->ativo) {
            return $this->statusBadge('Publicada', 'publicada');
        }

        if ($inicio->greaterThan($agora) && !$missa->ativo) {
            return $this->statusBadge('Rascunho', 'rascunho');
        }

        if ($fim->lessThan($agora) && $fim->isSameDay($agora)) {
            return $this->statusBadge('Encerrada', 'encerrada');
        }

        return $this->statusBadge('Historico', 'historico');
    }

    private function statusBadge(string $label, string $slug): array
    {
        return [
            'label' => $label,
            'slug' => $slug,
        ];
    }
}
