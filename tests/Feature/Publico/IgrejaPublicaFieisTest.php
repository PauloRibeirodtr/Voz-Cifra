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

class IgrejaPublicaFieisTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        CarbonImmutable::setTestNow();

        parent::tearDown();
    }

    public function test_link_publico_do_fiel_mostra_todas_as_missas_do_dia_mesmo_antes_do_horario(): void
    {
        CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-04-27 07:30:00', 'America/Cuiaba'));

        $igreja = Igreja::factory()->create([
            'nome' => 'Paroquia Sao Jose',
            'slug' => 'paroquia-sao-jose',
            'cidade' => 'Campo Grande',
            'estado' => 'MS',
            'ativo' => true,
        ]);

        Missa::query()->create([
            'igreja_id' => $igreja->id,
            'titulo' => 'Missa da Manha',
            'data_missa' => '2026-04-27',
            'hora_inicio' => '08:00:00',
            'hora_fim' => '09:00:00',
            'publica_para_fieis' => true,
            'publica_para_musicos' => false,
            'ativo' => true,
        ]);

        Missa::query()->create([
            'igreja_id' => $igreja->id,
            'titulo' => 'Missa da Noite',
            'data_missa' => '2026-04-27',
            'hora_inicio' => '19:00:00',
            'hora_fim' => '20:30:00',
            'publica_para_fieis' => true,
            'publica_para_musicos' => false,
            'ativo' => true,
        ]);

        $response = $this->get(route('igrejas.public.show', ['slug' => $igreja->slug]));

        $response->assertOk();
        $response->assertSee('Programação');
        $response->assertSee('Missa da Manha');
        $response->assertSee('Missa da Noite');
        $response->assertSeeInOrder(['08:00', 'Missa da Manha', '19:00', 'Missa da Noite'], false);
        $response->assertDontSee('Como funciona esta página');
    }

    public function test_pagina_principal_mostra_endereco_status_simples_e_botao_direto(): void
    {
        CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-04-27 07:30:00', 'America/Cuiaba'));

        $igreja = Igreja::factory()->create([
            'nome' => 'Paroquia Nossa Senhora dos Remedios',
            'slug' => 'paroquia-remedios',
            'cidade' => 'Ladario',
            'estado' => 'MS',
            'endereco' => 'Rua Cunha Couto, Centro',
            'cnpj' => '11.222.333/0001-44',
            'ativo' => true,
        ]);

        Missa::query()->create([
            'igreja_id' => $igreja->id,
            'titulo' => 'Missa de Sabado',
            'data_missa' => '2026-05-02',
            'hora_inicio' => '19:00:00',
            'hora_fim' => '20:00:00',
            'publica_para_fieis' => true,
            'publica_para_musicos' => false,
            'ativo' => true,
        ]);

        $this
            ->get(route('root'))
            ->assertOk()
            ->assertSee('Rua Cunha Couto, Centro')
            ->assertSee('Missa publicada')
            ->assertSee('Abrir próxima missa')
            ->assertDontSee('CNPJ')
            ->assertDontSee($igreja->cnpj);
    }

    public function test_paginas_publicas_expoem_metadados_para_busca_e_compartilhamento(): void
    {
        $igreja = Igreja::factory()->create([
            'nome' => 'Paroquia Nossa Senhora das Merces',
            'slug' => 'igreja-nossa-senhora-das-merces',
            'cidade' => 'Ladario',
            'estado' => 'MS',
            'ativo' => true,
        ]);

        Missa::query()->create([
            'igreja_id' => $igreja->id,
            'titulo' => 'Missa Dominical',
            'data_missa' => now('America/Cuiaba')->addDay()->toDateString(),
            'hora_inicio' => '19:00:00',
            'hora_fim' => '20:00:00',
            'publica_para_fieis' => true,
            'publica_para_musicos' => true,
            'ativo' => true,
        ]);

        $this
            ->get(route('igrejas.public.show', ['slug' => $igreja->slug]))
            ->assertOk()
            ->assertSee('<meta name="description"', false)
            ->assertSee('<link rel="canonical"', false)
            ->assertSee('property="og:title"', false)
            ->assertSee('application/ld+json', false)
            ->assertSee('Paroquia Nossa Senhora das Merces');
    }

    public function test_robots_e_sitemap_publicos_sao_gerados(): void
    {
        $igreja = Igreja::factory()->create([
            'nome' => 'Paroquia Sao Jose',
            'slug' => 'paroquia-sao-jose',
            'ativo' => true,
        ]);

        $this
            ->get(route('robots'))
            ->assertOk()
            ->assertSee('Disallow: /admin')
            ->assertSee(route('sitemap'));

        $this
            ->get(route('sitemap'))
            ->assertOk()
            ->assertSee('<urlset', false)
            ->assertSee(route('root'), false)
            ->assertSee(route('igrejas.public.show', ['slug' => $igreja->slug]), false);
    }

    public function test_link_publico_do_fiel_exibe_repertorio_sem_cifras(): void
    {
        CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-04-27 08:30:00', 'America/Cuiaba'));

        $igreja = Igreja::factory()->create([
            'slug' => 'paroquia-santa-cecilia',
            'ativo' => true,
        ]);

        $usuario = Usuario::factory()->create();

        $missa = Missa::query()->create([
            'igreja_id' => $igreja->id,
            'titulo' => 'Missa com Cantos',
            'data_missa' => '2026-04-27',
            'hora_inicio' => '09:00:00',
            'hora_fim' => '10:00:00',
            'publica_para_fieis' => true,
            'publica_para_musicos' => false,
            'ativo' => true,
        ]);

        $musica = Musica::query()->create([
            'titulo' => 'Aclamacao ao Evangelho',
            'artista' => null,
            'letra' => "[Am]Senhor, tende piedade\n[C]Cristo, tende piedade",
            'criado_por' => $usuario->id,
            'ativo' => true,
        ]);

        MissaMusica::query()->create([
            'missa_id' => $missa->id,
            'musica_id' => $musica->id,
            'versao_musical_id' => null,
            'tom_usado' => null,
            'momento_liturgico_id' => null,
            'ordem' => 1,
        ]);

        $response = $this->get(route('igrejas.public.show', ['slug' => $igreja->slug]));

        $response->assertOk();
        $response->assertSee('Missa com Cantos');
        $response->assertSee('Copiar link');
        $response->assertSee('WhatsApp');
        $response->assertSee('Senhor, tende piedade');
        $response->assertSee('Cristo, tende piedade');
        $response->assertDontSee('[Am]');
        $response->assertDontSee('[C]');
    }

    public function test_link_publico_do_fiel_usa_apenas_letra_base_e_reconhece_refrao_flexivel(): void
    {
        CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-04-27 08:30:00', 'America/Cuiaba'));

        $igreja = Igreja::factory()->create([
            'slug' => 'paroquia-refrao-fiel',
            'ativo' => true,
        ]);

        $usuario = Usuario::factory()->create();

        $missa = Missa::query()->create([
            'igreja_id' => $igreja->id,
            'titulo' => 'Missa com Refrao',
            'data_missa' => '2026-04-27',
            'hora_inicio' => '09:00:00',
            'hora_fim' => '10:00:00',
            'publica_para_fieis' => true,
            'publica_para_musicos' => false,
            'ativo' => true,
        ]);

        $musica = Musica::query()->create([
            'titulo' => 'Canto Base',
            'artista' => null,
            'letra' => "[C]Verso da letra base\nRef ,\n[G]Refrão da letra base",
            'criado_por' => $usuario->id,
            'ativo' => true,
        ]);

        $versao = VersaoMusical::query()->create([
            'musica_id' => $musica->id,
            'titulo' => 'Versao do musico',
            'tom_musical' => 'D',
            'letra_com_cifras' => "[D]Texto exclusivo da versao musical",
            'criado_por' => $usuario->id,
            'ativo' => true,
        ]);

        MissaMusica::query()->create([
            'missa_id' => $missa->id,
            'musica_id' => $musica->id,
            'versao_musical_id' => $versao->id,
            'tom_usado' => null,
            'momento_liturgico_id' => null,
            'ordem' => 1,
        ]);

        $response = $this->get(route('igrejas.public.show', ['slug' => $igreja->slug]));

        $response->assertOk();
        $response->assertSee('Verso da letra base');
        $response->assertSee('Refrão da letra base');
        $response->assertSee('lyrics-section-label--refrao', false);
        $response->assertDontSee('Texto exclusivo da versao musical');
        $response->assertDontSee('[C]');
        $response->assertDontSee('[G]');
    }

    public function test_link_publico_do_fiel_mostra_estado_vazio_util_quando_nao_ha_missas_hoje(): void
    {
        CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-04-27 10:00:00', 'America/Cuiaba'));

        $igreja = Igreja::factory()->create([
            'slug' => 'paroquia-sao-francisco',
            'ativo' => true,
        ]);

        Missa::query()->create([
            'igreja_id' => $igreja->id,
            'titulo' => 'Missa do Domingo Passado',
            'data_missa' => '2026-04-26',
            'hora_inicio' => '18:00:00',
            'hora_fim' => '19:00:00',
            'publica_para_fieis' => true,
            'publica_para_musicos' => false,
            'ativo' => true,
        ]);

        $response = $this->get(route('igrejas.public.show', ['slug' => $igreja->slug]));

        $response->assertOk();
        $response->assertSee('Programação');
        $response->assertSee('Ainda não há missas para hoje.', false);
        $response->assertSee('Consultar histórico', false);
        $response->assertSee('Missa do Domingo Passado');
    }

    public function test_link_publico_do_fiel_mostra_agenda_futura_publicada_do_trimestre(): void
    {
        CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-04-27 10:00:00', 'America/Cuiaba'));

        $igreja = Igreja::factory()->create([
            'slug' => 'paroquia-agenda-publica',
            'ativo' => true,
        ]);

        Missa::query()->create([
            'igreja_id' => $igreja->id,
            'titulo' => 'Missa da Proxima Semana',
            'data_missa' => '2026-05-04',
            'hora_inicio' => '19:00:00',
            'hora_fim' => '20:00:00',
            'publica_para_fieis' => true,
            'publica_para_musicos' => false,
            'ativo' => true,
        ]);

        Missa::query()->create([
            'igreja_id' => $igreja->id,
            'titulo' => 'Missa Fora do Trimestre',
            'data_missa' => '2026-08-10',
            'hora_inicio' => '19:00:00',
            'hora_fim' => '20:00:00',
            'publica_para_fieis' => true,
            'publica_para_musicos' => false,
            'ativo' => true,
        ]);

        $response = $this->get(route('igrejas.public.show', ['slug' => $igreja->slug]));

        $response->assertOk();
        $response->assertSee('Celebrações publicadas');
        $response->assertSee('Missa da Proxima Semana');
        $response->assertSee('Abrir celebração');
        $response->assertSee('Celebração ainda sem repertório público.');
        $response->assertDontSee('Missa Fora do Trimestre');

        $this
            ->get(route('igrejas.public.show', ['slug' => $igreja->slug, 'celebracao' => Missa::query()->where('titulo', 'Missa da Proxima Semana')->value('id')]))
            ->assertOk()
            ->assertSee('Missa da Proxima Semana');
    }
}
