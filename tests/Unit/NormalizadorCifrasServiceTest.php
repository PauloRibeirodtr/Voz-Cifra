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

    public function test_acorde_visual_depois_do_fim_da_frase_fica_no_final_da_linha(): void
    {
        $servico = new NormalizadorCifrasService();

        $texto = implode("\n", [
            '                                                                                Bm',
            'Sua alma é um bem que nunca envelhecerá',
        ]);

        $resultado = $servico->normalizarFormato($texto);

        $this->assertSame('Sua alma é um bem que nunca envelhecerá[Bm]', $resultado);
    }

    public function test_acorde_visual_no_inicio_e_no_fim_preserva_ponta_da_frase(): void
    {
        $servico = new NormalizadorCifrasService();

        $texto = implode("\n", [
            'D                                                                               Bm',
            'Sua alma é um bem que nunca envelhecerá',
        ]);

        $resultado = $servico->normalizarFormato($texto);

        $this->assertSame('[D]Sua alma é um bem que nunca envelhecerá[Bm]', $resultado);
    }

    public function test_intro_com_acordes_na_mesma_linha_vira_marcacao_e_linha_instrumental(): void
    {
        $servico = new NormalizadorCifrasService();

        $resultado = $servico->normalizarFormato('[Intro] G5  D/F#  Em  D9');

        $this->assertSame("[Intro]\n[G5] [D/F#] [Em] [D9]", $resultado);
        $this->assertSame(['G5', 'D/F#', 'Em', 'D9'], $servico->extrairAcordes($resultado));
    }

    public function test_acordes_complexos_com_baixo_sao_reconhecidos(): void
    {
        $servico = new NormalizadorCifrasService();

        $resultado = $servico->normalizarFormato('[Final] A9/G#  F#m(7/11)/C#  Em');

        $this->assertSame("[Final]\n[A9/G#] [F#m(7/11)/C#] [Em]", $resultado);
        $this->assertSame(['A9/G#', 'F#m(7/11)/C#', 'Em'], $servico->extrairAcordes($resultado));
    }

    public function test_virada_instrumental_com_parenteses_vira_linha_de_acordes(): void
    {
        $servico = new NormalizadorCifrasService();

        $resultado = $servico->normalizarFormato("( G5  D9  Am )");

        $this->assertSame("[G5] [D9] [Am]", $resultado);
        $this->assertSame(['G5', 'D9', 'Am'], $servico->extrairAcordes($resultado));
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

    public function test_refrao_digitado_com_abreviacoes_pontuacao_ou_parenteses_vira_marcacao_padrao(): void
    {
        $servico = new NormalizadorCifrasService();

        $casos = [
            'Ref',
            'ref',
            'ref.',
            'Ref:',
            'Ref ,',
            '[Ref]',
            '[ref]',
            '(Ref)',
            'Refrão',
            '[refrão]',
            '(Refrão)',
            'REFRAO',
            'refr',
            'refr.',
            'refrão.',
            'Refrão final',
            'Ref 2',
        ];

        foreach ($casos as $caso) {
            $resultado = $servico->normalizarFormato(implode("\n", [
                $caso,
                'D',
                'Primeira linha',
            ]));

            $this->assertSame(
                "Refrão:\n[D]\nPrimeira linha",
                $resultado,
                "Falhou ao normalizar a marcação: {$caso}"
            );
        }
    }

    public function test_palavra_parecida_com_refrao_nao_vira_marcacao(): void
    {
        $servico = new NormalizadorCifrasService();

        $resultado = $servico->normalizarFormato(implode("\n", [
            'Refazer este trecho depois',
            'D',
            'Primeira linha',
        ]));

        $this->assertSame("Refazer este trecho depois\nD\nPrimeira linha", $resultado);
    }
}
