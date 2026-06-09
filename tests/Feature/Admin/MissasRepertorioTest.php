<?php

namespace Tests\Feature\Admin;

use App\Enums\PapelIgreja;
use App\Models\Igreja;
use App\Models\Missa;
use App\Models\MomentoLiturgico;
use App\Models\Musica;
use App\Models\Usuario;
use App\Models\VersaoMusical;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MissasRepertorioTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_local_consegue_cadastrar_missa_sem_repertorio_inicial(): void
    {
        /** @var Igreja $igreja */
        $igreja = Igreja::factory()->create(['status_operacional' => 'operacional']);
        /** @var Usuario $adminLocal */
        $adminLocal = Usuario::factory()->create();
        $adminLocal->adicionarPapel(PapelIgreja::ADMIN_LOCAL, $igreja);

        $dataMissa = now('America/Cuiaba')->addDay()->toDateString();

        $this
            ->actingAs($adminLocal)
            ->withSession(['igreja_ativa_id' => $igreja->id])
            ->post(route('local-admin.missas.store'), [
                'titulo' => 'Missa de Domingo',
                'data_missa' => $dataMissa,
                'hora_inicio' => '19:00',
                'hora_fim' => '20:00',
                'ativo' => '1',
                'publica_para_fieis' => '1',
                'publica_para_musicos' => '1',
                'reaproveitar_repertorio' => '0',
            ])
            ->assertRedirect(route('local-admin.missas.show', Missa::query()->first()) . '#missa-repertorio')
            ->assertSessionHas('success');

        $this->assertDatabaseHas('missas', [
            'igreja_id' => $igreja->id,
            'titulo' => 'Missa de Domingo',
        ]);

        $this->assertSame($dataMissa, Missa::query()->where('titulo', 'Missa de Domingo')->firstOrFail()->data_missa->toDateString());
    }

    public function test_adicionar_musica_reorganiza_repertorio_pela_ordem_do_momento(): void
    {
        /** @var Igreja $igreja */
        $igreja = Igreja::factory()->create(['status_operacional' => 'operacional']);
        /** @var Usuario $adminLocal */
        $adminLocal = Usuario::factory()->create();
        $adminLocal->adicionarPapel(PapelIgreja::ADMIN_LOCAL, $igreja);

        $entrada = MomentoLiturgico::query()->create([
            'nome' => 'Entrada',
            'ordem_exibicao' => 1,
            'ativo' => true,
        ]);
        $final = MomentoLiturgico::query()->create([
            'nome' => 'Final',
            'ordem_exibicao' => 99,
            'ativo' => true,
        ]);

        $missa = Missa::query()->create([
            'igreja_id' => $igreja->id,
            'titulo' => 'Missa de Domingo',
            'data_missa' => now('America/Cuiaba')->addDay()->toDateString(),
            'hora_inicio' => '19:00',
            'hora_fim' => '20:00',
            'ativo' => true,
        ]);

        $musicaFinal = Musica::query()->create([
            'titulo' => 'Canto Final',
            'letra' => 'Ide em paz',
            'momento_liturgico_id' => $final->id,
            'criado_por' => $adminLocal->id,
            'ativo' => true,
        ]);
        $musicaEntrada = Musica::query()->create([
            'titulo' => 'Canto de Entrada',
            'letra' => 'Vamos celebrar',
            'momento_liturgico_id' => $entrada->id,
            'criado_por' => $adminLocal->id,
            'ativo' => true,
        ]);

        $this
            ->actingAs($adminLocal)
            ->withSession(['igreja_ativa_id' => $igreja->id])
            ->post(route('local-admin.repertorio.store', $missa), [
                'musica_id' => $musicaFinal->id,
                'momento_liturgico_id' => $final->id,
            ])
            ->assertRedirect(route('local-admin.missas.show', $missa) . '#missa-repertorio');

        $this
            ->actingAs($adminLocal)
            ->withSession(['igreja_ativa_id' => $igreja->id])
            ->post(route('local-admin.repertorio.store', $missa), [
                'musica_id' => $musicaEntrada->id,
                'momento_liturgico_id' => $entrada->id,
            ])
            ->assertRedirect(route('local-admin.missas.show', $missa) . '#missa-repertorio');

        $this->assertDatabaseHas('missa_musicas', [
            'missa_id' => $missa->id,
            'musica_id' => $musicaEntrada->id,
            'ordem' => 1,
        ]);
        $this->assertDatabaseHas('missa_musicas', [
            'missa_id' => $missa->id,
            'musica_id' => $musicaFinal->id,
            'ordem' => 2,
        ]);
    }

    public function test_nao_permite_adicionar_a_mesma_musica_duas_vezes_na_missa(): void
    {
        /** @var Igreja $igreja */
        $igreja = Igreja::factory()->create(['status_operacional' => 'operacional']);
        /** @var Usuario $adminLocal */
        $adminLocal = Usuario::factory()->create();
        $adminLocal->adicionarPapel(PapelIgreja::ADMIN_LOCAL, $igreja);

        $momento = MomentoLiturgico::query()->create([
            'nome' => 'Entrada',
            'ordem_exibicao' => 1,
            'ativo' => true,
        ]);
        $missa = Missa::query()->create([
            'igreja_id' => $igreja->id,
            'titulo' => 'Missa de Domingo',
            'data_missa' => now('America/Cuiaba')->addDay()->toDateString(),
            'hora_inicio' => '19:00',
            'hora_fim' => '20:00',
            'ativo' => true,
        ]);
        $musica = Musica::query()->create([
            'titulo' => 'Canto de Entrada',
            'letra' => 'Vamos celebrar',
            'momento_liturgico_id' => $momento->id,
            'criado_por' => $adminLocal->id,
            'ativo' => true,
        ]);

        $this
            ->actingAs($adminLocal)
            ->withSession(['igreja_ativa_id' => $igreja->id])
            ->post(route('local-admin.repertorio.store', $missa), [
                'musica_id' => $musica->id,
                'momento_liturgico_id' => $momento->id,
            ])
            ->assertRedirect(route('local-admin.missas.show', $missa) . '#missa-repertorio');

        $this
            ->actingAs($adminLocal)
            ->withSession(['igreja_ativa_id' => $igreja->id])
            ->from(route('local-admin.missas.show', $missa))
            ->post(route('local-admin.repertorio.store', $missa), [
                'musica_id' => $musica->id,
                'momento_liturgico_id' => $momento->id,
            ])
            ->assertRedirect(route('local-admin.missas.show', $missa))
            ->assertSessionHasErrors('musica_id');

        $this->assertSame(1, (int) $missa->missaMusicas()->where('musica_id', $musica->id)->count());
    }

    public function test_corrigir_ordem_do_repertorio_reorganiza_itens_existentes(): void
    {
        /** @var Igreja $igreja */
        $igreja = Igreja::factory()->create(['status_operacional' => 'operacional']);
        /** @var Usuario $adminLocal */
        $adminLocal = Usuario::factory()->create();
        $adminLocal->adicionarPapel(PapelIgreja::ADMIN_LOCAL, $igreja);

        $entrada = MomentoLiturgico::query()->create([
            'nome' => 'Entrada',
            'ordem_exibicao' => 1,
            'ativo' => true,
        ]);
        $final = MomentoLiturgico::query()->create([
            'nome' => 'Final',
            'ordem_exibicao' => 99,
            'ativo' => true,
        ]);

        $missa = Missa::query()->create([
            'igreja_id' => $igreja->id,
            'titulo' => 'Missa de Domingo',
            'data_missa' => now('America/Cuiaba')->addDay()->toDateString(),
            'hora_inicio' => '19:00',
            'hora_fim' => '20:00',
            'ativo' => true,
        ]);
        $musicaEntrada = Musica::query()->create([
            'titulo' => 'Canto de Entrada',
            'letra' => 'Vamos celebrar',
            'momento_liturgico_id' => $entrada->id,
            'criado_por' => $adminLocal->id,
            'ativo' => true,
        ]);
        $musicaFinal = Musica::query()->create([
            'titulo' => 'Canto Final',
            'letra' => 'Ide em paz',
            'momento_liturgico_id' => $final->id,
            'criado_por' => $adminLocal->id,
            'ativo' => true,
        ]);

        $missa->missaMusicas()->create([
            'musica_id' => $musicaFinal->id,
            'momento_liturgico_id' => $final->id,
            'ordem' => 1,
        ]);
        $missa->missaMusicas()->create([
            'musica_id' => $musicaEntrada->id,
            'momento_liturgico_id' => $entrada->id,
            'ordem' => 2,
        ]);

        $this
            ->actingAs($adminLocal)
            ->withSession(['igreja_ativa_id' => $igreja->id])
            ->post(route('local-admin.missas.repertorio.corrigir-ordem', $missa))
            ->assertRedirect(route('local-admin.missas.show', $missa) . '#missa-repertorio')
            ->assertSessionHas('success');

        $this->assertDatabaseHas('missa_musicas', [
            'missa_id' => $missa->id,
            'musica_id' => $musicaEntrada->id,
            'ordem' => 1,
        ]);
        $this->assertDatabaseHas('missa_musicas', [
            'missa_id' => $missa->id,
            'musica_id' => $musicaFinal->id,
            'ordem' => 2,
        ]);
    }

    public function test_tela_da_missa_mostra_textos_de_fallback_sem_entidade_html_quebrada(): void
    {
        /** @var Igreja $igreja */
        $igreja = Igreja::factory()->create(['status_operacional' => 'operacional']);
        /** @var Usuario $adminLocal */
        $adminLocal = Usuario::factory()->create();
        $adminLocal->adicionarPapel(PapelIgreja::ADMIN_LOCAL, $igreja);

        $momento = MomentoLiturgico::query()->create([
            'nome' => 'Entrada',
            'ordem_exibicao' => 1,
            'ativo' => true,
        ]);
        $missa = Missa::query()->create([
            'igreja_id' => $igreja->id,
            'titulo' => 'Missa de Domingo',
            'data_missa' => now('America/Cuiaba')->addDay()->toDateString(),
            'hora_inicio' => '19:00',
            'hora_fim' => '20:00',
            'ativo' => true,
        ]);
        $musica = Musica::query()->create([
            'titulo' => 'Estaremos Aqui Reunidos',
            'artista' => null,
            'letra' => 'Letra base',
            'momento_liturgico_id' => $momento->id,
            'criado_por' => $adminLocal->id,
            'ativo' => true,
        ]);
        $versao = VersaoMusical::query()->create([
            'musica_id' => $musica->id,
            'titulo' => 'Principal',
            'tom_musical' => 'E',
            'letra_com_cifras' => '[E]Letra base',
            'criado_por' => $adminLocal->id,
            'ativo' => true,
        ]);

        $this
            ->actingAs($adminLocal)
            ->withSession(['igreja_ativa_id' => $igreja->id])
            ->post(route('local-admin.repertorio.store', $missa), [
                'musica_id' => $musica->id,
                'momento_liturgico_id' => $momento->id,
            ]);

        $this->assertDatabaseHas('missa_musicas', [
            'missa_id' => $missa->id,
            'musica_id' => $musica->id,
            'versao_musical_id' => $versao->id,
            'tom_usado' => null,
        ]);

        $this
            ->actingAs($adminLocal)
            ->withSession(['igreja_ativa_id' => $igreja->id])
            ->get(route('local-admin.missas.show', $missa))
            ->assertOk()
            ->assertSee('Artista não informado')
            ->assertDontSee('n&amp;atilde;o');
    }

    public function test_admin_local_duplica_missa_para_outra_igreja_que_administra(): void
    {
        /** @var Igreja $igrejaOrigem */
        $igrejaOrigem = Igreja::factory()->create(['status_operacional' => 'operacional']);
        /** @var Igreja $igrejaDestino */
        $igrejaDestino = Igreja::factory()->create(['status_operacional' => 'operacional']);
        /** @var Usuario $adminLocal */
        $adminLocal = Usuario::factory()->create();
        $adminLocal->adicionarPapel(PapelIgreja::ADMIN_LOCAL, $igrejaOrigem);
        $adminLocal->adicionarPapel(PapelIgreja::ADMIN_LOCAL, $igrejaDestino);

        $momento = MomentoLiturgico::query()->create([
            'nome' => 'Entrada',
            'ordem_exibicao' => 1,
            'ativo' => true,
        ]);
        $musica = Musica::query()->create([
            'titulo' => 'Canto de Entrada',
            'letra' => 'Vamos celebrar',
            'momento_liturgico_id' => $momento->id,
            'criado_por' => $adminLocal->id,
            'ativo' => true,
        ]);
        $versao = VersaoMusical::query()->create([
            'musica_id' => $musica->id,
            'titulo' => 'Principal',
            'tom_musical' => 'D',
            'letra_com_cifras' => '[D]Vamos celebrar',
            'criado_por' => $adminLocal->id,
            'ativo' => true,
        ]);
        $missaOrigem = Missa::query()->create([
            'igreja_id' => $igrejaOrigem->id,
            'titulo' => 'Missa de Domingo',
            'data_missa' => now('America/Cuiaba')->addDay()->toDateString(),
            'hora_inicio' => '19:00',
            'hora_fim' => '20:00',
            'ativo' => true,
            'publica_para_fieis' => true,
            'publica_para_musicos' => true,
        ]);
        $missaOrigem->missaMusicas()->create([
            'musica_id' => $musica->id,
            'versao_musical_id' => $versao->id,
            'tom_usado' => 'E',
            'momento_liturgico_id' => $momento->id,
            'ordem' => 1,
        ]);

        $dataNovaMissa = now('America/Cuiaba')->addDays(7)->toDateString();

        $this
            ->actingAs($adminLocal)
            ->withSession(['igreja_ativa_id' => $igrejaOrigem->id])
            ->post(route('local-admin.missas.duplicar', $missaOrigem), [
                'igreja_destino_id' => $igrejaDestino->id,
                'titulo' => 'Missa replicada',
                'data_missa' => $dataNovaMissa,
                'hora_inicio' => '18:00',
                'hora_fim' => '19:30',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $novaMissa = Missa::query()
            ->where('igreja_id', $igrejaDestino->id)
            ->where('titulo', 'Missa replicada')
            ->firstOrFail();

        $this->assertFalse((bool) $novaMissa->ativo);
        $this->assertFalse((bool) $novaMissa->publica_para_fieis);
        $this->assertFalse((bool) $novaMissa->publica_para_musicos);
        $this->assertDatabaseHas('missa_musicas', [
            'missa_id' => $novaMissa->id,
            'musica_id' => $musica->id,
            'versao_musical_id' => $versao->id,
            'tom_usado' => 'E',
            'momento_liturgico_id' => $momento->id,
            'ordem' => 1,
        ]);
    }

    public function test_admin_local_nao_duplica_missa_para_igreja_sem_permissao(): void
    {
        /** @var Igreja $igrejaOrigem */
        $igrejaOrigem = Igreja::factory()->create(['status_operacional' => 'operacional']);
        /** @var Igreja $igrejaSemPermissao */
        $igrejaSemPermissao = Igreja::factory()->create(['status_operacional' => 'operacional']);
        /** @var Usuario $adminLocal */
        $adminLocal = Usuario::factory()->create();
        $adminLocal->adicionarPapel(PapelIgreja::ADMIN_LOCAL, $igrejaOrigem);

        $missaOrigem = Missa::query()->create([
            'igreja_id' => $igrejaOrigem->id,
            'titulo' => 'Missa de Domingo',
            'data_missa' => now('America/Cuiaba')->addDay()->toDateString(),
            'hora_inicio' => '19:00',
            'hora_fim' => '20:00',
            'ativo' => true,
        ]);

        $this
            ->actingAs($adminLocal)
            ->withSession(['igreja_ativa_id' => $igrejaOrigem->id])
            ->from(route('local-admin.missas.show', $missaOrigem))
            ->post(route('local-admin.missas.duplicar', $missaOrigem), [
                'igreja_destino_id' => $igrejaSemPermissao->id,
                'titulo' => 'Tentativa indevida',
                'data_missa' => now('America/Cuiaba')->addDays(7)->toDateString(),
                'hora_inicio' => '18:00',
                'hora_fim' => '19:30',
            ])
            ->assertRedirect(route('local-admin.missas.show', $missaOrigem))
            ->assertSessionHasErrors('igreja_destino_id');

        $this->assertDatabaseMissing('missas', [
            'igreja_id' => $igrejaSemPermissao->id,
            'titulo' => 'Tentativa indevida',
        ]);
    }

    public function test_concluir_repertorio_com_pendencias_mantem_usuario_na_missa_com_aviso(): void
    {
        /** @var Igreja $igreja */
        $igreja = Igreja::factory()->create(['status_operacional' => 'operacional']);
        /** @var Usuario $adminLocal */
        $adminLocal = Usuario::factory()->create();
        $adminLocal->adicionarPapel(PapelIgreja::ADMIN_LOCAL, $igreja);

        $missa = Missa::query()->create([
            'igreja_id' => $igreja->id,
            'titulo' => 'Missa de Domingo',
            'data_missa' => now('America/Cuiaba')->addDay()->toDateString(),
            'hora_inicio' => '19:00',
            'hora_fim' => '20:00',
            'ativo' => true,
        ]);
        $musica = Musica::query()->create([
            'titulo' => 'Canto sem cifra',
            'letra' => 'Letra base',
            'criado_por' => $adminLocal->id,
            'ativo' => true,
        ]);
        $missa->missaMusicas()->create([
            'musica_id' => $musica->id,
            'ordem' => 1,
        ]);

        $this
            ->actingAs($adminLocal)
            ->withSession(['igreja_ativa_id' => $igreja->id])
            ->post(route('local-admin.missas.concluir-montagem', $missa))
            ->assertRedirect(route('local-admin.missas.show', $missa) . '#missa-repertorio')
            ->assertSessionHas('warning')
            ->assertSessionHas('missa_pendencias');
    }
}
