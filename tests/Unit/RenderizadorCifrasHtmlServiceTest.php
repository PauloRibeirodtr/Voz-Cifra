<?php

namespace Tests\Unit;

use App\Services\RenderizadorCifrasHtmlService;
use PHPUnit\Framework\TestCase;

class RenderizadorCifrasHtmlServiceTest extends TestCase
{
    public function test_linha_instrumental_com_acordes_em_colchetes_nao_vira_marcacao(): void
    {
        $servico = new RenderizadorCifrasHtmlService();

        $html = $servico->renderizar("[Intro]\n[G5] [D/F#] [Em] [D9]");

        $this->assertStringContainsString('cifra-marcacao', $html);
        $this->assertStringContainsString('cifra-linha--acordes', $html);
        $this->assertStringContainsString('>G5<', $html);
        $this->assertStringContainsString('>D/F#<', $html);
        $this->assertStringNotContainsString('G5] [D/F#', $html);
    }
}
