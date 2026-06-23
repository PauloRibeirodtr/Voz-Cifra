<?php

namespace App\Services;

use Illuminate\Support\Str;

class RenderizadorLetrasHtmlService
{
    public function normalizar(string $texto): string
    {
        $textoNormalizado = str_replace(['\\n', "\r\n", "\r"], "\n", trim($texto));
        $textoNormalizado = preg_replace("/[ \t]+\n/", "\n", $textoNormalizado) ?? $textoNormalizado;

        return preg_replace("/\n{3,}/", "\n\n", $textoNormalizado) ?? $textoNormalizado;
    }

    public function removerCifras(string $texto): string
    {
        $linhas = preg_split('/\n/', $this->normalizar($texto)) ?: [];
        $linhasSemCifras = [];

        foreach ($linhas as $linha) {
            $linhaSemCifras = preg_replace_callback(
                '/\[([^\[\]\r\n]+)\]/',
                fn (array $matches): string => $this->pareceAcorde((string) $matches[1])
                    ? ''
                    : trim((string) $matches[1]),
                (string) $linha
            ) ?? (string) $linha;

            $linhaSemCifras = preg_replace('/[ \t]{2,}/', ' ', trim($linhaSemCifras)) ?? trim($linhaSemCifras);

            if ($linhaSemCifras === '' || $this->ehLinhaSomenteAcordes($linhaSemCifras)) {
                $linhasSemCifras[] = '';
                continue;
            }

            $linhasSemCifras[] = $this->extrairMarcacaoComCifras($linhaSemCifras) ?? $linhaSemCifras;
        }

        return $this->normalizar(implode("\n", $linhasSemCifras));
    }

    public function renderizarSemCifras(string $texto): string
    {
        $linhas = preg_split('/\n/', $this->normalizar($texto)) ?: [];
        $html = [];
        $paragrafoAtual = [];
        $paragrafoRefrao = false;
        $proximoParagrafoRefrao = false;

        $fecharParagrafo = function () use (&$html, &$paragrafoAtual, &$paragrafoRefrao): void {
            if ($paragrafoAtual === []) {
                return;
            }

            $classe = $paragrafoRefrao ? 'lyrics-stanza lyrics-stanza--refrao' : 'lyrics-stanza';
            $linhas = array_map(fn (string $linha): string => e($linha), $paragrafoAtual);
            $html[] = '<div class="' . $classe . '"><p>' . implode('<br>', $linhas) . '</p></div>';
            $paragrafoAtual = [];
            $paragrafoRefrao = false;
        };

        foreach ($linhas as $linha) {
            $linhaLimpa = trim((string) $linha);

            if ($linhaLimpa === '') {
                $fecharParagrafo();
                $html[] = '<div class="lyrics-space"></div>';
                continue;
            }

            $marcacao = $this->extrairMarcacao($linhaLimpa);

            if ($marcacao !== null) {
                $fecharParagrafo();
                $html[] = '<div class="' . $this->classeMarcacao($marcacao) . '">' . e($marcacao) . '</div>';
                $proximoParagrafoRefrao = $this->ehMarcacaoRefrao($marcacao);
                continue;
            }

            if ($paragrafoAtual === []) {
                $paragrafoRefrao = $proximoParagrafoRefrao;
                $proximoParagrafoRefrao = false;
            }

            $paragrafoAtual[] = $linhaLimpa;
        }

        $fecharParagrafo();

        return implode('', $html);
    }

    private function extrairMarcacao(string $linha): ?string
    {
        if (preg_match('/^\[(.+)\]$/u', $linha, $matches) === 1 && !$this->pareceAcorde((string) $matches[1])) {
            return trim((string) $matches[1]);
        }

        if (preg_match('/^\((.+)\)$/u', $linha, $matches) === 1 && !$this->pareceAcorde((string) $matches[1])) {
            return trim((string) $matches[1]);
        }

        return $this->ehMarcacaoSecao($linha) ? $linha : null;
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

        return preg_match('/^(intro|pre[-\s]?refrao|entrada|final|ponte|estrofe|verso|primeira parte|segunda parte|terceira parte)(?::|\s|$)/', $normalizado) === 1;
    }

    private function classeMarcacao(string $valor): string
    {
        return $this->ehMarcacaoRefrao($valor)
            ? 'lyrics-section-label lyrics-section-label--refrao'
            : 'lyrics-section-label';
    }

    private function ehMarcacaoRefrao(string $valor): bool
    {
        return $this->ehMarcacaoRefraoNormalizada($this->normalizarMarcacao($valor));
    }

    private function normalizarMarcacao(string $valor): string
    {
        $normalizado = Str::of($valor)->ascii()->lower()->trim()->toString();
        $normalizado = preg_replace('/[^a-z0-9:.\-\s]/', '', $normalizado) ?? $normalizado;

        return trim((string) preg_replace('/\s+/', ' ', $normalizado));
    }

    private function ehMarcacaoRefraoNormalizada(string $normalizado): bool
    {
        return preg_match('/^(?:ref|refr|refrao)\.?:?(?:\s+(?:[0-9]+|[ivx]+|bis|final))?$/', $normalizado) === 1;
    }

    private function pareceAcorde(string $valor): bool
    {
        $valor = trim($valor);

        if ($valor === '' || str_contains($valor, ' ')) {
            return false;
        }

        return preg_match('/^[A-G](?:#|b)?(?:(?:maj|min|dim|aug|sus|add|omit|no|m|M|º|°|\+|-|[0-9#b])|\([^\)\]]+\))*(?:\/[A-G](?:#|b)?)?$/u', $valor) === 1;
    }

    private function ehLinhaSomenteAcordes(string $linha): bool
    {
        $texto = trim($linha);
        $texto = preg_replace('/^\((.*)\)$/u', '$1', $texto) ?? $texto;

        if ($texto === '') {
            return false;
        }

        $tokens = array_values(array_filter(preg_split('/\s+/', $texto) ?: [], function (string $token): bool {
            return !in_array(trim($token), ['(', ')'], true);
        }));

        if ($tokens === []) {
            return false;
        }

        foreach ($tokens as $token) {
            if (!$this->pareceAcorde(trim((string) $token, " \t,;[]"))) {
                return false;
            }
        }

        return true;
    }

    private function extrairMarcacaoComCifras(string $linha): ?string
    {
        $tokens = array_values(array_filter(preg_split('/\s+/', trim($linha)) ?: []));

        if (count($tokens) < 2) {
            return null;
        }

        $primeiroToken = trim((string) $tokens[0], " \t:[]");

        if (!$this->ehMarcacaoSecao($primeiroToken)) {
            return null;
        }

        $restante = trim(implode(' ', array_slice($tokens, 1)));

        return $this->ehLinhaSomenteAcordes($restante) ? $primeiroToken : null;
    }
}
