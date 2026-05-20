<?php

namespace Tests\Unit;

use App\Services\NormalizadorCifrasService;
use PHPUnit\Framework\TestCase;

class NormalizadorCifrasServiceTest extends TestCase
{
    public function test_conversao_de_linha_visual_preserva_acorde_no_meio_da_palavra(): void
    {
        $servico = new NormalizadorCifrasService();

        $texto = implode("\n", [
            '      Dm           G7',
            "1. Bendize, o minh'alma, ao Senhor!",
        ]);

        $resultado = $servico->normalizarFormato($texto);

        $this->assertSame("1. Ben[Dm]dize, o minh'[G7]alma, ao Senhor!", $resultado);
    }
}
