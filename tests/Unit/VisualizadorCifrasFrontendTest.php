<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class VisualizadorCifrasFrontendTest extends TestCase
{
    public function test_linha_vazia_usa_espacador_com_estilo_proprio(): void
    {
        $renderizador = file_get_contents(__DIR__.'/../../public/js/cifra/chord-transposer.js');
        $cssAplicacao = file_get_contents(__DIR__.'/../../resources/css/app.css');
        $cssPublicoMusicos = file_get_contents(__DIR__.'/../../resources/css/publico/music.css');

        $this->assertIsString($renderizador);
        $this->assertIsString($cssAplicacao);
        $this->assertIsString($cssPublicoMusicos);
        $this->assertStringContainsString(
            '<div class="cifra-espaco-estrofe" aria-hidden="true"></div>',
            $renderizador
        );
        $this->assertStringContainsString('.cifra-espaco-estrofe', $cssAplicacao);
        $this->assertStringContainsString('.lyrics .cifra-espaco-estrofe', $cssPublicoMusicos);
    }
}
