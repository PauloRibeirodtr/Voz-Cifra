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
            $marcacaoEAcordes = $this->separarMarcacaoEAcordes($linhaAtual);
            $marcacaoNormalizada = $this->normalizarMarcacaoLinha($linhaAtual);

            if ($marcacaoEAcordes !== null) {
                $linhasNormalizadas[] = $marcacaoEAcordes['marcacao'];
                $linhasNormalizadas[] = $marcacaoEAcordes['acordes'];
                continue;
            }

            if ($marcacaoNormalizada !== null) {
                $linhasNormalizadas[] = $marcacaoNormalizada;
                continue;
            }

            if ($this->ehLinhaApenasAcordes($linhaAtual) && $this->linhaAnteriorEhMarcacao($linhas, $indice)) {
                $linhasNormalizadas[] = $this->converterLinhaSomenteAcordesParaCifras($linhaAtual);
                continue;
            }

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

            if ($this->ehLinhaSomenteAcordes($linhaAtual)) {
                $linhasNormalizadas[] = $this->converterLinhaSomenteAcordesParaCifras($linhaAtual);
                continue;
            }

            $linhasNormalizadas[] = $linhaAtual;
        }

        return rtrim(implode("\n", $linhasNormalizadas));
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
            $acordeNormalizado = $this->normalizarTokenAcorde($acorde);

            if ($acordeNormalizado === null) {
                continue;
            }

            $posicao = $this->localizarPosicaoSeguraNaLetra($resultado, $offset);
            $resultado = substr($resultado, 0, $posicao) . '[' . $acordeNormalizado . ']' . substr($resultado, $posicao);
        }

        return $resultado;
    }

    private function converterLinhaSomenteAcordesParaCifras(string $linhaAcordes): string
    {
        $linhaAcordes = $this->limparLinhaAcordes($linhaAcordes);
        $indentacao = '';

        if (preg_match('/^\s*/', $linhaAcordes, $matches) === 1) {
            $indentacao = $matches[0] ?? '';
        }

        $tokens = preg_split('/\s+/', trim($linhaAcordes), -1, PREG_SPLIT_NO_EMPTY) ?: [];

        return $indentacao . implode(' ', array_map(
            fn (string $token): string => ($acorde = $this->normalizarTokenAcorde($token)) !== null
                ? '[' . $acorde . ']'
                : $token,
            $tokens
        ));
    }

    private function separarMarcacaoEAcordes(string $linha): ?array
    {
        $linha = trim($linha);

        if (!preg_match('/^\[([^\[\]\r\n]+)\]\s+(.+)$/', $linha, $matches)) {
            return null;
        }

        $marcacao = trim($matches[1]);
        $resto = trim($matches[2]);

        if ($this->ehAcorde($marcacao) || !$this->ehLinhaApenasAcordes($resto)) {
            return null;
        }

        return [
            'marcacao' => '[' . $marcacao . ']',
            'acordes' => $this->converterLinhaSomenteAcordesParaCifras($resto),
        ];
    }

    private function localizarPosicaoSeguraNaLetra(string $linhaLetra, int $offset): int
    {
        preg_match_all('/\S+/u', $linhaLetra, $matches, PREG_OFFSET_CAPTURE);

        $palavras = $matches[0] ?? [];

        if ($palavras === []) {
            return 0;
        }

        if ($offset >= strlen($linhaLetra)) {
            return strlen($linhaLetra);
        }

        foreach ($palavras as [$palavra, $inicio]) {
            $fim = $inicio + strlen($palavra);

            if ($offset < $inicio) {
                return $inicio;
            }

            if ($offset <= $fim) {
                return $offset;
            }
        }

        return strlen($linhaLetra);
    }

    private function ehLinhaSomenteAcordes(string $linha): bool
    {
        $linhaOriginal = $linha;

        if (!$this->ehLinhaApenasAcordes($linha)) {
            return false;
        }

        $tokens = preg_split('/\s+/', trim($linha)) ?: [];

        if (count($tokens) === 1 && !preg_match('/^\s+/', $linhaOriginal)) {
            return false;
        }

        return true;
    }

    private function ehLinhaApenasAcordes(string $linha): bool
    {
        $linha = trim($this->limparLinhaAcordes($linha));

        if ($linha === '' || $this->ehLinhaTablatura($linha)) {
            return false;
        }

        $tokens = preg_split('/\s+/', $linha) ?: [];

        if ($tokens === []) {
            return false;
        }

        foreach ($tokens as $token) {
            if ($this->normalizarTokenAcorde($token) === null) {
                return false;
            }
        }

        return true;
    }

    private function linhaAnteriorEhMarcacao(array $linhas, int $indiceAtual): bool
    {
        for ($indice = $indiceAtual - 1; $indice >= 0; $indice--) {
            $linha = trim((string) ($linhas[$indice] ?? ''));

            if ($linha === '') {
                continue;
            }

            return $this->ehMarcacaoSecao($linha);
        }

        return false;
    }

    private function ehMarcacaoSecao(string $linha): bool
    {
        $linha = trim($linha);

        if (preg_match('/^\[(.+)\]$/', $linha, $matches) && !$this->ehAcorde($matches[1])) {
            $linha = trim($matches[1]);
        }

        $normalizada = $this->normalizarTextoMarcacao($linha);

        if (strlen($normalizada) > 32) {
            return false;
        }

        if ($this->ehMarcacaoRefraoNormalizada($normalizada)) {
            return true;
        }

        return (bool) preg_match('/^(intro|pre[-\s]?refrao:?|entrada|final|ponte|estrofe|verso|primeira parte|segunda parte|terceira parte)(?:\s|$)/', $normalizada);
    }

    private function normalizarMarcacaoLinha(string $linha): ?string
    {
        $linha = trim($linha);

        if ($linha === '') {
            return null;
        }

        if (preg_match('/^\[(.+)\]$/', $linha, $matches) && !$this->ehAcorde($matches[1])) {
            $linha = trim($matches[1]);
        }

        $normalizada = $this->normalizarTextoMarcacao($linha);

        if ($this->ehMarcacaoRefraoNormalizada($normalizada)) {
            return 'Refrão:';
        }

        return null;
    }

    private function ehMarcacaoRefraoNormalizada(string $normalizada): bool
    {
        $normalizada = trim((string) preg_replace('/\s+/', ' ', $normalizada));

        return (bool) preg_match('/^(?:ref|refr|refrao)\.?:?(?:\s+(?:[0-9]+|[ivx]+|bis|final))?$/', $normalizada);
    }

    private function normalizarTextoMarcacao(string $texto): string
    {
        $normalizada = mb_strtolower($texto);
        $transliterada = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $normalizada);

        if (is_string($transliterada)) {
            $normalizada = $transliterada;
        }

        $normalizada = str_replace('~', '', $normalizada);
        $normalizada = (string) preg_replace('/[^a-z0-9:.\-\s]/', '', $normalizada);

        return trim((string) preg_replace('/\s+/', ' ', $normalizada));
    }

    private function normalizarTokenAcorde(string $token): ?string
    {
        $token = trim($token);
        $token = preg_replace('/^[([]+/', '', $token) ?? $token;
        $token = preg_replace('/[)\],.;]+$/', '', $token) ?? $token;

        if (preg_match('/^\[([^\[\]\r\n]+)\]$/', $token, $matches)) {
            $token = trim($matches[1]);
        }

        return $this->ehAcorde($token) ? $token : null;
    }

    private function limparLinhaAcordes(string $linha): string
    {
        $linha = rtrim($linha);
        $linha = (string) preg_replace('/^(\s*)\(\s*/', '$1', $linha);
        $linha = (string) preg_replace('/\s*\)\s*$/', '', $linha);
        $linha = (string) preg_replace('/[|]+\s*$/', '', $linha);

        return rtrim($linha);
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

        return (bool) preg_match('/^[A-G](?:#|b)?(?:(?:maj|min|dim|aug|sus|add|omit|no|m|M|º|°|\\+|-|[0-9#b])|\\([^\\)\\]]+\\))*(?:\\/[A-G](?:#|b)?(?:(?:maj|min|dim|aug|sus|add|omit|no|m|M|º|°|\\+|-|[0-9#b])|\\([^\\)\\]]+\\))*)?$/u', $valor);
    }

    private function normalizarQuebrasDeLinha(string $texto): string
    {
        return rtrim(str_replace(["\r\n", "\r"], "\n", $texto));
    }
}
