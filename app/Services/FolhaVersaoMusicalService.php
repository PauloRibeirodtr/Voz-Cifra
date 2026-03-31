<?php

namespace App\Services;

use App\Models\Acorde;
use App\Models\VersaoMusical;

class FolhaVersaoMusicalService
{
    public function __construct(
        private readonly RenderizadorCifrasHtmlService $renderizadorCifrasHtmlService,
        private readonly RenderizadorDiagramaAcordeSvgService $renderizadorDiagramaAcordeSvgService
    ) {
    }

    public function montar(VersaoMusical $versaoMusical, string $textoCifrado, ?string $tomExibicao, array $meta = []): array
    {
        $acordesEncontrados = $this->extrairAcordes($textoCifrado);
        $acordes = Acorde::query()
            ->where('ativo', true)
            ->whereIn('nome', $acordesEncontrados)
            ->orderBy('nome')
            ->get()
            ->map(fn (Acorde $acorde): array => [
                'nome' => $acorde->nome,
                'descricao' => $acorde->descricao,
                'svg' => $this->renderizadorDiagramaAcordeSvgService->renderizar($acorde->dados_diagrama),
            ])
            ->filter(fn (array $acorde): bool => $acorde['svg'] !== null)
            ->values()
            ->all();

        return [
            'titulo' => $versaoMusical->musica?->titulo ?: ($meta['titulo'] ?? 'Musica'),
            'subtitulo' => $versaoMusical->titulo ?: 'Versao principal',
            'html_cifra' => $this->renderizadorCifrasHtmlService->renderizar($textoCifrado),
            'tom_original' => $versaoMusical->tom_musical,
            'tom_exibicao' => $tomExibicao,
            'bpm' => $versaoMusical->bpm,
            'youtube_video_id' => $versaoMusical->youtube_video_id,
            'meta' => $meta,
            'acordes' => $acordes,
        ];
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
}
