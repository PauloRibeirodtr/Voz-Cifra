<?php

namespace App\Services;

class RenderizadorCifrasHtmlService
{
    public function renderizar(string $texto): string
    {
        $linhas = preg_split('/\r\n|\r|\n/', trim($texto)) ?: [];
        $proximaLinhaRefrao = false;
        $blocoAtualRefrao = false;

        return implode('', array_map(function (string $linha) use (&$proximaLinhaRefrao, &$blocoAtualRefrao): string {
            $linha = rtrim($linha);
            $linhaLimpa = trim($linha);

            if ($linhaLimpa === '') {
                $blocoAtualRefrao = false;
                return '<div class="cifra-espaco"></div>';
            }

            $marcacaoEAcordes = $this->extrairMarcacaoEAcordes($linhaLimpa);
            if ($marcacaoEAcordes !== null) {
                $blocoAtualRefrao = false;
                $proximaLinhaRefrao = $this->ehMarcacaoRefrao($marcacaoEAcordes['marcacao']);

                return '<div class="' . $this->classeMarcacaoSecao($marcacaoEAcordes['marcacao']) . '">' . e($marcacaoEAcordes['marcacao']) . '</div>'
                    . '<div class="cifra-linha cifra-linha--acordes"><span class="cifra-acordes">' . $this->renderizarAcordesInline($marcacaoEAcordes['acordes']) . '</span></div>';
            }

            $acordesComColchetes = $this->extrairLinhaSomenteAcordesComColchetes($linhaLimpa);
            if ($acordesComColchetes !== null) {
                $indentacao = strlen($linha) - strlen(ltrim($linha));

                return '<div class="cifra-linha cifra-linha--acordes' . ($blocoAtualRefrao ? ' cifra-linha--refrao' : '') . '" style="--cifra-indent:' . $indentacao . 'ch"><span class="cifra-acordes">' . $this->renderizarAcordesInline($acordesComColchetes) . '</span></div>';
            }

            if ($this->extrairMarcacaoIsolada($linhaLimpa, $matches) && ! $this->ehAcorde($matches[1])) {
                $blocoAtualRefrao = false;
                $proximaLinhaRefrao = $this->ehMarcacaoRefrao((string) $matches[1]);

                return '<div class="' . $this->classeMarcacaoSecao($matches[1]) . '">' . e($matches[1]) . '</div>';
            }

            if ($this->ehMarcacaoSecao($linhaLimpa)) {
                $blocoAtualRefrao = false;
                $proximaLinhaRefrao = $this->ehMarcacaoRefrao($linhaLimpa);

                return '<div class="' . $this->classeMarcacaoSecao($linhaLimpa) . '">' . e($linhaLimpa) . '</div>';
            }

            if ($proximaLinhaRefrao) {
                $blocoAtualRefrao = true;
                $proximaLinhaRefrao = false;
            }

            if ($this->ehLinhaSomenteAcordes($linha)) {
                $indentacao = strlen($linha) - strlen(ltrim($linha));
                $acordes = preg_split('/\s+/', trim($linha)) ?: [];

                return '<div class="cifra-linha cifra-linha--acordes' . ($blocoAtualRefrao ? ' cifra-linha--refrao' : '') . '" style="--cifra-indent:' . $indentacao . 'ch"><span class="cifra-acordes">' . $this->renderizarAcordesInline($acordes) . '</span></div>';
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

            return '<div class="cifra-linha' . ($blocoAtualRefrao ? ' cifra-linha--refrao' : '') . '">' . $segmentos . '</div>';
        }, $linhas));
    }

    private function renderizarSegmento(array $acordes, string $texto): string
    {
        $htmlAcordes = $this->renderizarAcordesInline($acordes);

        return '<span class="cifra-segmento"><span class="cifra-acordes">' . $htmlAcordes . '</span><span class="cifra-letra">' . e($texto) . '</span></span>';
    }

    private function renderizarAcordesInline(array $acordes): string
    {
        return implode(' ', array_map(
            fn (string $acorde): string => '<span class="cifra-acorde">' . e($acorde) . '</span>',
            $acordes
        ));
    }

    private function ehLinhaSomenteAcordes(string $valor): bool
    {
        $partes = preg_split('/\s+/', trim($valor)) ?: [];
        $partes = array_values(array_filter($partes, fn (string $parte): bool => $parte !== ''));

        return $partes !== [] && collect($partes)->every(fn (string $parte): bool => $this->ehAcorde($parte));
    }

    private function extrairLinhaSomenteAcordesComColchetes(string $valor): ?array
    {
        $linha = trim($valor);

        if ($linha === '' || preg_match('/^(?:\[[^\[\]\r\n]+\]\s*)+$/u', $linha) !== 1) {
            return null;
        }

        preg_match_all('/\[([^\[\]\r\n]+)\]/u', $linha, $matches);
        $acordes = array_map('trim', $matches[1] ?? []);

        if ($acordes === [] || ! collect($acordes)->every(fn (string $acorde): bool => $this->ehAcorde($acorde))) {
            return null;
        }

        return $acordes;
    }

    private function extrairMarcacaoEAcordes(string $valor): ?array
    {
        if (preg_match('/^\[([^\[\]\r\n]+)\]\s+(.+)$/u', trim($valor), $matches) !== 1) {
            return null;
        }

        $marcacao = trim($matches[1]);
        $resto = trim($matches[2]);

        if ($this->ehAcorde($marcacao)) {
            return null;
        }

        $acordes = $this->extrairLinhaSomenteAcordesComColchetes($resto);
        if ($acordes === null && $this->ehLinhaSomenteAcordes($resto)) {
            $acordes = preg_split('/\s+/', $resto) ?: [];
        }

        if ($acordes === null || $acordes === []) {
            return null;
        }

        return [
            'marcacao' => $marcacao,
            'acordes' => $acordes,
        ];
    }

    private function ehAcorde(string $valor): bool
    {
        $valor = trim($valor);

        if ($valor === '' || str_contains($valor, ' ')) {
            return false;
        }

        return preg_match('/^[A-G](?:#|b)?(?:(?:maj|min|dim|aug|sus|add|omit|no|m|M|º|°|\\+|-|[0-9#b])|\\([^\\)\\]]+\\))*(?:\\/[A-G](?:#|b)?(?:(?:maj|min|dim|aug|sus|add|omit|no|m|M|º|°|\\+|-|[0-9#b])|\\([^\\)\\]]+\\))*)?$/u', $valor) === 1;
    }

    private function ehMarcacaoSecao(string $valor): bool
    {
        $normalizado = $this->normalizarMarcacao($valor);

        if (strlen($normalizado) > 32) {
            return false;
        }

        if ($this->ehMarcacaoRefraoNormalizada($normalizado)) {
            return true;
        }

        return preg_match('/^(intro|pre[-\s]?refrao|entrada|final|ponte|estrofe|verso|primeira parte|segunda parte|terceira parte)(:|\s|$)/', $normalizado) === 1;
    }

    private function classeMarcacaoSecao(string $valor): string
    {
        return $this->ehMarcacaoRefrao($valor)
            ? 'cifra-marcacao cifra-marcacao--refrao'
            : 'cifra-marcacao';
    }

    private function ehMarcacaoRefrao(string $valor): bool
    {
        return $this->ehMarcacaoRefraoNormalizada($this->normalizarMarcacao($valor));
    }

    private function normalizarMarcacao(string $valor): string
    {
        $valor = trim($valor);

        if (preg_match('/^[\[\(]([^\[\]\(\)\r\n]+)[\]\)]$/u', $valor, $matches) === 1) {
            $valor = trim($matches[1]);
        }

        $semAcentos = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $valor);
        $normalizado = strtolower(trim($semAcentos !== false ? $semAcentos : $valor));
        $normalizado = preg_replace('/[^a-z0-9:.\-\s]/', '', $normalizado) ?? $normalizado;

        return trim((string) preg_replace('/\s+/', ' ', $normalizado));
    }

    private function ehMarcacaoRefraoNormalizada(string $normalizado): bool
    {
        return preg_match('/^(?:ref|refr|refrao)\.?:?(?:\s+(?:[0-9]+|[ivx]+|bis|final))?$/', $normalizado) === 1;
    }

    private function extrairMarcacaoIsolada(string $valor, ?array &$matches = null): bool
    {
        if (preg_match('/^\[([^\[\]\r\n]+)\]$/u', $valor, $matches) === 1) {
            return true;
        }

        return preg_match('/^\(([^\(\)\r\n]+)\)$/u', $valor, $matches) === 1;
    }
}
