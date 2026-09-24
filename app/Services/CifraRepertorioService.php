<?php

namespace App\Services;

use App\Models\MissaMusica;
use App\Models\VersaoMusical;

class CifraRepertorioService
{
    public function __construct(
        private readonly TranspositorCifrasService $transpositorCifrasService
    ) {}

    /**
     * @return array{
     *     versao: VersaoMusical|null,
     *     tom_original: string|null,
     *     tom_exibicao: string|null,
     *     passos: int,
     *     texto: string
     * }
     */
    public function resolver(MissaMusica $item, bool $permitirVersaoAlternativa = false): array
    {
        $versao = $item->versaoMusical;

        if ($permitirVersaoAlternativa && ! $this->versaoPossuiCifra($versao)) {
            $versao = $item->musica?->versoesMusicais
                ?->first(fn (VersaoMusical $versaoMusical): bool => $this->versaoPossuiCifra($versaoMusical));
        }

        $tomOriginal = $versao?->tom_musical;
        $tomExibicao = $item->tom_usado ?: $tomOriginal;
        $passos = $this->transpositorCifrasService->calcularPassos($tomOriginal, $tomExibicao);

        return [
            'versao' => $versao,
            'tom_original' => $tomOriginal,
            'tom_exibicao' => $this->transpositorCifrasService->transporTomExibicao($tomOriginal, $tomExibicao),
            'passos' => $passos,
            'texto' => $this->transpositorCifrasService->transporTextoCifrado(
                $versao?->letra_com_cifras,
                $passos
            ),
        ];
    }

    private function versaoPossuiCifra(?VersaoMusical $versao): bool
    {
        return $versao !== null && trim((string) $versao->letra_com_cifras) !== '';
    }
}
