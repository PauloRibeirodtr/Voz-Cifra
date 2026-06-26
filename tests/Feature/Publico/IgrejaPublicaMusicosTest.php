<?php

namespace Tests\Feature\Publico;

use App\Models\Igreja;
use App\Models\Missa;
use App\Models\MissaMusica;
use App\Models\Musica;
use App\Models\Usuario;
use App\Models\VersaoMusical;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IgrejaPublicaMusicosTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        CarbonImmutable::setTestNow();

        parent::tearDown();
    }

    public function test_rota_publica_do_musico_usa_o_mesmo_slug_base_da_igreja(): void
    {
        $igreja = Igreja::factory()->create([
            'slug' => 'paroquia-santa-cecilia',
            'slug_publico_musicos' => 'paroquia-santa-cecilia-musicos',
            'ativo' => true,
        ]);

        $this->assertSame(
            '/paroquia-santa-cecilia/musicos',
            route('igrejas.public.musicos.show', ['slug' => $igreja->slug], false)
        );

        $this->get(route('igrejas.public.musicos.show', ['slug' => $igreja->slug]))
            ->assertOk();
    }

    public function test_link_publico_do_musico_mostra_repertorio_publicado_com_cifras(): void
    {
        CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-04-27 18:00:00', 'America/Cuiaba'));

        $igreja = Igreja::factory()->create([
            'slug' => 'paroquia-sao-joao',
            'slug_publico_musicos' => 'paroquia-sao-joao-musicos',
            'ativo' => true,
        ]);

        $usuario = Usuario::factory()->create();

        $missa = Missa::query()->create([
            'igreja_id' => $igreja->id,
            'titulo' => 'Missa do Ensaio',
            'data_missa' => '2026-04-27',
            'hora_inicio' => '19:00:00',
            'hora_fim' => '20:30:00',
            'publica_para_fieis' => false,
            'publica_para_musicos' => true,
            'ativo' => true,
        ]);

        $musica = Musica::query()->create([
            'titulo' => 'Canto de Entrada',
            'artista' => null,
            'letra' => "[Am]Entrai, cantai\n[F]Louvai ao Senhor",
            'criado_por' => $usuario->id,
            'ativo' => true,
        ]);

        MissaMusica::query()->create([
            'missa_id' => $missa->id,
            'musica_id' => $musica->id,
            'versao_musical_id' => null,
            'tom_usado' => 'Am',
            'momento_liturgico_id' => null,
            'ordem' => 1,
        ]);

        $response = $this->get(route('igrejas.public.musicos.show', ['slug' => $igreja->slug]));

        $response->assertOk();
        $response->assertSee('Celebrações publicadas');
        $response->assertSee('Missa do Ensaio');
        $response->assertSee('[Am]', false);
        $response->assertSee('[F]', false);
        $response->assertSee('Entrai, cantai');
        $response->assertSee('Abrir repertório');
        $response->assertSee('Copiar link');
        $response->assertSee('WhatsApp');
        $response->assertSee('Tom Am');
        $response->assertSee('data-public-musician-lyrics', false);
        $response->assertDontSee('data-public-scroll-toggle', false);
        $response->assertSee('js/cifra/chord-transposer.js', false);
        $response->assertDontSee('Favoritar');
        $response->assertDontSee('Salvar');
        $response->assertDontSee('Criar coleção');
    }

    public function test_link_publico_do_musico_usa_versao_vinculada_e_reconhece_refrao_flexivel(): void
    {
        CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-04-27 18:00:00', 'America/Cuiaba'));

        $igreja = Igreja::factory()->create([
            'slug' => 'paroquia-refrao-musico',
            'slug_publico_musicos' => 'paroquia-refrao-musico-musicos',
            'ativo' => true,
        ]);

        $usuario = Usuario::factory()->create();

        $missa = Missa::query()->create([
            'igreja_id' => $igreja->id,
            'titulo' => 'Missa dos Musicos',
            'data_missa' => '2026-04-27',
            'hora_inicio' => '19:00:00',
            'hora_fim' => '20:30:00',
            'publica_para_fieis' => false,
            'publica_para_musicos' => true,
            'ativo' => true,
        ]);

        $musica = Musica::query()->create([
            'titulo' => 'Canto com Versao',
            'artista' => null,
            'letra' => "[C]Texto da letra base",
            'criado_por' => $usuario->id,
            'ativo' => true,
        ]);

        $versao = VersaoMusical::query()->create([
            'musica_id' => $musica->id,
            'titulo' => 'Tom do ensaio',
            'tom_musical' => 'D',
            'letra_com_cifras' => "[Ref ,]\n[D]Refrão da versão musical",
            'criado_por' => $usuario->id,
            'ativo' => true,
        ]);

        MissaMusica::query()->create([
            'missa_id' => $missa->id,
            'musica_id' => $musica->id,
            'versao_musical_id' => $versao->id,
            'tom_usado' => 'D',
            'momento_liturgico_id' => null,
            'ordem' => 1,
        ]);

        $response = $this->get(route('igrejas.public.musicos.show', ['slug' => $igreja->slug]));

        $response->assertOk();
        $response->assertSee('Refrão da versão musical');
        $response->assertSee('lyrics-section-label--refrao', false);
        $response->assertSee('[D]', false);
        $response->assertDontSee('Texto da letra base');
    }

    public function test_link_publico_do_musico_mostra_estado_vazio_quando_nao_ha_publicacao_para_ensaio(): void
    {
        CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-04-27 12:00:00', 'America/Cuiaba'));

        $igreja = Igreja::factory()->create([
            'slug' => 'paroquia-santa-luzia',
            'slug_publico_musicos' => 'paroquia-santa-luzia-musicos',
            'ativo' => true,
        ]);

        $response = $this->get(route('igrejas.public.musicos.show', ['slug' => $igreja->slug]));

        $response->assertOk();
        $response->assertSee('Ainda não há missas publicadas para ensaio.');
        $response->assertSee('somente leitura', false);
    }
}
