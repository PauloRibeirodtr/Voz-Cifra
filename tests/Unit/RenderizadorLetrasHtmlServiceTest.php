<?php

namespace Tests\Unit;

use App\Services\RenderizadorLetrasHtmlService;
use PHPUnit\Framework\TestCase;

class RenderizadorLetrasHtmlServiceTest extends TestCase
{
    public function test_refrao_com_parenteses_ou_abreviacao_recebe_destaque_sem_cifras(): void
    {
        $servico = new RenderizadorLetrasHtmlService();

        $htmlComParenteses = $servico->renderizarSemCifras("(Ref)\nLinha do canto");
        $htmlComVirgula = $servico->renderizarSemCifras("Ref ,\nLinha do canto");

        $this->assertStringContainsString('lyrics-section-label--refrao', $htmlComParenteses);
        $this->assertStringContainsString('lyrics-stanza--refrao', $htmlComParenteses);
        $this->assertStringContainsString('lyrics-section-label--refrao', $htmlComVirgula);
        $this->assertStringContainsString('lyrics-stanza--refrao', $htmlComVirgula);
    }

    public function test_palavra_parecida_com_refrao_nao_recebe_destaque_sem_cifras(): void
    {
        $servico = new RenderizadorLetrasHtmlService();

        $html = $servico->renderizarSemCifras("Refazer este trecho depois\nLinha do canto");

        $this->assertStringNotContainsString('lyrics-section-label--refrao', $html);
        $this->assertStringNotContainsString('lyrics-stanza--refrao', $html);
    }
}
