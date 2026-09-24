<?php

namespace Tests\Unit;

use App\Models\MissaMusica;
use App\Models\Musica;
use App\Models\VersaoMusical;
use App\Services\CifraRepertorioService;
use App\Services\TranspositorCifrasService;
use PHPUnit\Framework\TestCase;

class CifraRepertorioServiceTest extends TestCase
{
    public function test_resolve_tom_e_texto_transposto_para_o_repertorio(): void
    {
        $versao = new VersaoMusical([
            'tom_musical' => 'D',
            'letra_com_cifras' => '[D]Presença [Bm]real [G]de [A]Jesus',
        ]);
        $item = new MissaMusica(['tom_usado' => 'E']);
        $item->setRelation('versaoMusical', $versao);

        $cifra = $this->service()->resolver($item);

        $this->assertSame('D', $cifra['tom_original']);
        $this->assertSame('E', $cifra['tom_exibicao']);
        $this->assertSame(2, $cifra['passos']);
        $this->assertSame('[E]Presença [C#m]real [A]de [B]Jesus', $cifra['texto']);
    }

    public function test_usa_o_tom_original_da_versao_alternativa_que_sera_exibida(): void
    {
        $versaoSemCifra = new VersaoMusical([
            'tom_musical' => 'D',
            'letra_com_cifras' => '',
        ]);
        $versaoAlternativa = new VersaoMusical([
            'tom_musical' => 'C',
            'letra_com_cifras' => '[C]Canto [Am]alternativo',
        ]);
        $musica = new Musica;
        $musica->setRelation('versoesMusicais', collect([$versaoAlternativa]));
        $item = new MissaMusica(['tom_usado' => 'E']);
        $item->setRelation('versaoMusical', $versaoSemCifra);
        $item->setRelation('musica', $musica);

        $cifra = $this->service()->resolver($item, true);

        $this->assertSame($versaoAlternativa, $cifra['versao']);
        $this->assertSame('C', $cifra['tom_original']);
        $this->assertSame('E', $cifra['tom_exibicao']);
        $this->assertSame('[E]Canto [C#m]alternativo', $cifra['texto']);
    }

    private function service(): CifraRepertorioService
    {
        return new CifraRepertorioService(new TranspositorCifrasService);
    }
}
