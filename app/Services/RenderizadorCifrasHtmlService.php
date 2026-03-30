<?php

namespace App\Services;

class RenderizadorCifrasHtmlService
{
    public function renderizar(string $texto): string
    {
        $linhas = preg_split('/\r\n|\r|\n/', trim($texto)) ?: [];

        return implode('', array_map(function (string $linha): string {
            $linha = rtrim($linha);
            $linhaLimpa = trim($linha);

            if ($linhaLimpa === '') {
                return '<div class="cifra-espaco"></div>';
            }

            if (preg_match('/^\[(.+)\]$/u', $linhaLimpa, $matches) === 1 && !$this->ehAcorde($matches[1])) {
                return '<div class="cifra-marcacao">' . e($matches[1]) . '</div>';
            }

            preg_match_all('/\[([^\[\]\r\n]+)\]/', $linha, $matches, PREG_OFFSET_CAPTURE);

            $ultimoIndice = 0;
            $acordesPendentes = [];
            $segmentos = '';

            foreach ($matches[1] ?? [] as $indice => $match) {
                $valor = trim((string) ($match[0] ?? ''));
                $posicaoValor = (int) ($match[1] ?? 0);
                $textoAntes = substr($linha, $ultimoIndice, max(0, $posicaoValor - 1 - $ultimoIndice));

                if ($textoAntes !== '') {
                    $segmentos .= $this->renderizarSegmento($acordesPendentes, $textoAntes);
                    $acordesPendentes = [];
                }

                if ($this->ehAcorde($valor)) {
                    $acordesPendentes[] = $valor;
                } else {
                    $segmentos .= $this->renderizarSegmento([], '[' . $valor . ']');
                }

                $posicaoMatch = (int) ($matches[0][$indice][1] ?? 0);
                $matchCompleto = (string) ($matches[0][$indice][0] ?? '');
                $ultimoIndice = $posicaoMatch + strlen($matchCompleto);
            }

            $textoDepois = substr($linha, $ultimoIndice);

            if ($textoDepois !== '' || $acordesPendentes !== []) {
                $segmentos .= $this->renderizarSegmento($acordesPendentes, $textoDepois !== '' ? $textoDepois : ' ');
            }

            return '<div class="cifra-linha">' . $segmentos . '</div>';
        }, $linhas));
    }

    private function renderizarSegmento(array $acordes, string $texto): string
    {
        $htmlAcordes = implode(' ', array_map(
            fn (string $acorde): string => '<span class="cifra-acorde">' . e($acorde) . '</span>',
            $acordes
        ));

        return '<span class="cifra-segmento"><span class="cifra-acordes">' . $htmlAcordes . '</span><span class="cifra-letra">' . e($texto) . '</span></span>';
    }

    private function ehAcorde(string $valor): bool
    {
        $valor = trim($valor);

        if ($valor === '' || str_contains($valor, ' ')) {
            return false;
        }

        return preg_match('/^[A-G](?:#|b)?(?:(?:maj|min|dim|aug|sus|add|omit|no|m|M|º|°|\\+|-|[0-9#b])|\\([^\\)\\]]+\\))*(?:\\/[A-G](?:#|b)?)?$/', $valor) === 1;
    }
}


