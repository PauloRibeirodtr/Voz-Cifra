<?php

namespace App\Services;

class NormalizadorCifrasService
{
    public function processar(string $texto, array $acordesValidos = []): array
    {
        $textoOriginal = $this->normalizarQuebrasDeLinha($texto);
        $textoNormalizado = $this->normalizarFormato($textoOriginal);
        $acordesEncontrados = $this->extrairAcordes($textoNormalizado);
        $acordesInvalidos = $this->identificarAcordesInvalidos($acordesEncontrados, $acordesValidos);

        return [
            'texto_original' => $textoOriginal,
            'texto_normalizado' => $textoNormalizado,
            'texto_sem_cifras' => $this->removerCifras($textoNormalizado),
            'acordes_encontrados' => $acordesEncontrados,
            'acordes_invalidos' => $acordesInvalidos,
            'houve_conversao' => $textoNormalizado !== $textoOriginal,
        ];
    }

    public function removerCifras(string $texto): string
    {
        return trim((string) preg_replace_callback(
            '/\[([^\[\]\r\n]+)\]/',
            fn (array $matches): string => $this->ehAcorde($matches[1]) ? '' : $matches[0],
            $texto
        ));
    }

    public function extrairAcordes(string $texto): array
    {
        preg_match_all('/\[([^\[\]\r\n]+)\]/', $texto, $matches);

        $acordes = [];

        foreach ($matches[1] ?? [] as $possivelAcorde) {
            $possivelAcorde = trim($possivelAcorde);

            if ($this->ehAcorde($possivelAcorde)) {
                $acordes[] = $possivelAcorde;
            }
        }

        return array_values(array_unique($acordes));
    }

    public function normalizarFormato(string $texto): string
    {
        $linhas = preg_split('/\n/', $this->normalizarQuebrasDeLinha($texto)) ?: [];
        $linhasNormalizadas = [];

        for ($indice = 0; $indice < count($linhas); $indice++) {
            $linhaAtual = rtrim($linhas[$indice]);
            $proximaLinha = $linhas[$indice + 1] ?? null;

            if (
                $this->ehLinhaSomenteAcordes($linhaAtual)
                && $proximaLinha !== null
                && trim($proximaLinha) !== ''
                && !$this->ehLinhaSomenteAcordes($proximaLinha)
                && !$this->ehLinhaTablatura($proximaLinha)
            ) {
                $linhasNormalizadas[] = $this->combinarLinhaDeAcordesComLetra($linhaAtual, rtrim($proximaLinha));
                $indice++;
                continue;
            }

            $linhasNormalizadas[] = $linhaAtual;
        }

        return trim(implode("\n", $linhasNormalizadas));
    }

    private function identificarAcordesInvalidos(array $acordesEncontrados, array $acordesValidos): array
    {
        $acordesValidos = array_map('mb_strtoupper', $acordesValidos);

        return array_values(array_filter(
            $acordesEncontrados,
            fn (string $acorde): bool => !in_array(mb_strtoupper($acorde), $acordesValidos, true)
        ));
    }

    private function combinarLinhaDeAcordesComLetra(string $linhaAcordes, string $linhaLetra): string
    {
        preg_match_all('/\S+/', $linhaAcordes, $matches, PREG_OFFSET_CAPTURE);

        if (empty($matches[0])) {
            return $linhaLetra;
        }

        $resultado = $linhaLetra;

        foreach (array_reverse($matches[0]) as $match) {
            [$acorde, $offset] = $match;

            if (!$this->ehAcorde($acorde)) {
                continue;
            }

            $posicao = $this->localizarPosicaoSeguraNaLetra($resultado, $offset);
            $resultado = substr($resultado, 0, $posicao) . '[' . $acorde . ']' . substr($resultado, $posicao);
        }

        return $resultado;
    }

    private function localizarPosicaoSeguraNaLetra(string $linhaLetra, int $offset): int
    {
        preg_match_all('/\S+/u', $linhaLetra, $matches, PREG_OFFSET_CAPTURE);

        $palavras = $matches[0] ?? [];

        if ($palavras === []) {
            return 0;
        }

        $ultimaPalavra = $palavras[array_key_last($palavras)] ?? null;

        foreach ($palavras as $indice => [$palavra, $inicio]) {
            $fim = $inicio + strlen($palavra);

            if ($offset <= $inicio) {
                return $inicio;
            }

            if ($offset > $inicio && $offset < $fim) {
                return $inicio;
            }
        }

        return $ultimaPalavra[1] ?? 0;
    }

    private function ehLinhaSomenteAcordes(string $linha): bool
    {
        $linhaOriginal = $linha;
        $linha = trim($linha);

        if ($linha === '' || $this->ehLinhaTablatura($linha)) {
            return false;
        }

        $tokens = preg_split('/\s+/', $linha) ?: [];

        if ($tokens === []) {
            return false;
        }

        foreach ($tokens as $token) {
            if (!$this->ehAcorde($token)) {
                return false;
            }
        }

        if (count($tokens) === 1 && !preg_match('/^\s+/', $linhaOriginal)) {
            return false;
        }

        return true;
    }

    private function ehLinhaTablatura(string $linha): bool
    {
        $linha = trim($linha);

        return (bool) preg_match('/^[EABDGBe]\|/', $linha)
            || str_contains($linha, '|---')
            || str_contains($linha, 'Parte ');
    }

    private function ehAcorde(string $valor): bool
    {
        $valor = trim($valor);

        if ($valor === '' || str_contains($valor, ' ')) {
            return false;
        }

        return (bool) preg_match('/^[A-G](?:#|b)?(?:[a-zA-Z0-9º°+\-]*(?:\([^)\]]+\))?)?(?:\/[A-G](?:#|b)?)?$/', $valor);
    }

    private function normalizarQuebrasDeLinha(string $texto): string
    {
        return str_replace(["\r\n", "\r"], "\n", trim($texto));
    }
}
