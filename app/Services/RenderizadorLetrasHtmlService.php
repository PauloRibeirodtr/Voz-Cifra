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
        $textoSemCifras = preg_replace_callback(
            '/\[([^\[\]\r\n]+)\]/',
            fn (array $matches): string => $this->pareceAcorde((string) $matches[1])
                ? ''
                : trim((string) $matches[1]),
            $texto
        ) ?? $texto;

        return $this->normalizar($textoSemCifras);
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
            $html[] = '<div class="' . $classe . '"><p>' . e(implode(' ', $paragrafoAtual)) . '</p></div>';
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

        return $this->ehMarcacaoSecao($linha) ? $linha : null;
    }

    private function ehMarcacaoSecao(string $valor): bool
    {
        $normalizado = $this->normalizarMarcacao($valor);

        return strlen($normalizado) <= 32
            && preg_match('/^(refrao:?|refr\.?|ref:|entrada|final|ponte|estrofe|verso)(?:\s|$)/', $normalizado) === 1;
    }

    private function classeMarcacao(string $valor): string
    {
        return $this->ehMarcacaoRefrao($valor)
            ? 'lyrics-section-label lyrics-section-label--refrao'
            : 'lyrics-section-label';
    }

    private function ehMarcacaoRefrao(string $valor): bool
    {
        return preg_match('/^(refrao:?|refr\.?|ref:)(?:\s|$)/', $this->normalizarMarcacao($valor)) === 1;
    }

    private function normalizarMarcacao(string $valor): string
    {
        return Str::of($valor)->ascii()->lower()->trim()->toString();
    }

    private function pareceAcorde(string $valor): bool
    {
        $valor = trim($valor);

        if ($valor === '' || str_contains($valor, ' ')) {
            return false;
        }

        return preg_match('/^[A-G](?:#|b)?(?:(?:maj|min|dim|aug|sus|add|omit|no|m|M|º|°|\+|-|[0-9#b])|\([^\)\]]+\))*(?:\/[A-G](?:#|b)?)?$/u', $valor) === 1;
    }
}
