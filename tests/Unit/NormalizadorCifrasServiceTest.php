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

    public function test_linha_de_preparacao_sem_letra_tambem_vira_cifra(): void
    {
        $servico = new NormalizadorCifrasService();

        $texto = implode("\n", [
            '        A7',
            '',
            '      Dm           G7',
            "1. Bendize, o minh'alma, ao Senhor!",
        ]);

        $resultado = $servico->normalizarFormato($texto);

        $this->assertSame("        [A7]\n\n1. Ben[Dm]dize, o minh'[G7]alma, ao Senhor!", $resultado);
        $this->assertSame(['A7', 'Dm', 'G7'], $servico->extrairAcordes($resultado));
    }

    public function test_linha_de_preparacao_com_varios_acordes_apos_marcacao_nao_gruda_na_letra(): void
    {
        $servico = new NormalizadorCifrasService();

        $texto = implode("\n", [
            'Refrão:',
            'D A Am [D] [C]',
            'Vem Dar-Nos Teu Filho, Senhor,',
        ]);

        $resultado = $servico->normalizarFormato($texto);

        $this->assertSame("Refrão:\n[D] [A] [Am] [D] [C]\nVem Dar-Nos Teu Filho, Senhor,", $resultado);
        $this->assertSame(['D', 'A', 'Am', 'C'], $servico->extrairAcordes($resultado));
    }

    public function test_refrain_pode_repetir_e_ser_digitado_sem_acento_ou_colchetes(): void
    {
        $servico = new NormalizadorCifrasService();

        $texto = implode("\n", [
            'refrao',
            'D',
            'Primeira linha',
            '',
            '[REFRAO]',
            '[Dm] [G7]',
            'Outra linha',
        ]);

        $resultado = $servico->normalizarFormato($texto);

        $this->assertSame("Refrão:\n[D]\nPrimeira linha\n\nRefrão:\n[Dm] [G7]\nOutra linha", $resultado);
        $this->assertSame(['D', 'Dm', 'G7'], $servico->extrairAcordes($resultado));
    }
}
