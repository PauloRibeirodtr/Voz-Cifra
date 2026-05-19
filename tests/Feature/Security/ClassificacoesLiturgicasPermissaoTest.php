<?php

// cSpell:ignore classificacoes liturgicas liturgico coordenador musico usuario

namespace Tests\Feature\Security;

use App\Enums\PapelIgreja;
use App\Models\Igreja;
use App\Models\MomentoLiturgico;
use App\Models\TempoLiturgico;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClassificacoesLiturgicasPermissaoTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_master_pode_criar_tempo_liturgico_global(): void
    {
        /** @var Usuario $adminMaster */
        $adminMaster = Usuario::factory()->adminMaster()->create();

        $this
            ->actingAs($adminMaster)
            ->post(route('admin.tempos-liturgicos.store'), [
                'nome' => 'Advento',
                'descricao' => 'Tempo de preparacao.',
                'ativo' => '1',
            ])
            ->assertRedirect(route('admin.tempos-liturgicos.index'));

        $this->assertTrue(TempoLiturgico::query()->where('nome', 'Advento')->exists());
    }

    public function test_coordenador_pode_criar_momento_liturgico_global(): void
    {
        /** @var Igreja $igreja */
        $igreja = Igreja::factory()->create();
        /** @var Usuario $coordenador */
        $coordenador = Usuario::factory()->create();
        $coordenador->adicionarPapel(PapelIgreja::COORDENADOR, $igreja);

        $this
            ->actingAs($coordenador)
            ->withSession(['igreja_ativa_id' => $igreja->id])
            ->post(route('coordenador.momentos-liturgicos.store'), [
                'nome' => 'Entrada',
                'descricao' => 'Inicio da celebracao.',
                'ordem_exibicao' => '1',
                'ativo' => '1',
            ])
            ->assertRedirect(route('coordenador.momentos-liturgicos.index'));

        $this->assertTrue(MomentoLiturgico::query()->where('nome', 'Entrada')->exists());
    }

    public function test_musico_nao_pode_criar_classificacao_liturgica(): void
    {
        /** @var Igreja $igreja */
        $igreja = Igreja::factory()->create();
        /** @var Usuario $musico */
        $musico = Usuario::factory()->create();
        $musico->adicionarPapel(PapelIgreja::MUSICO, $igreja);

        $this
            ->actingAs($musico)
            ->withSession(['igreja_ativa_id' => $igreja->id])
            ->post(route('coordenador.tempos-liturgicos.store'), [
                'nome' => 'Tempo Indevido',
                'descricao' => 'Nao deve ser criado.',
                'ativo' => '1',
            ])
            ->assertRedirect(route('member.dashboard'));

        $this->assertFalse(TempoLiturgico::query()->where('nome', 'Tempo Indevido')->exists());
    }

    public function test_sidebar_mostra_classificacoes_liturgicas_apenas_para_perfis_permitidos(): void
    {
        /** @var Usuario $adminMaster */
        $adminMaster = Usuario::factory()->adminMaster()->create();
        /** @var Igreja $igreja */
        $igreja = Igreja::factory()->create();
        /** @var Usuario $coordenador */
        $coordenador = Usuario::factory()->create();
        $coordenador->adicionarPapel(PapelIgreja::COORDENADOR, $igreja);
        /** @var Usuario $musico */
        $musico = Usuario::factory()->create();
        $musico->adicionarPapel(PapelIgreja::MUSICO, $igreja);

        $this
            ->actingAs($adminMaster)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee(route('admin.tempos-liturgicos.index'), false)
            ->assertSee(route('admin.momentos-liturgicos.index'), false);

        $this
            ->actingAs($coordenador)
            ->withSession(['igreja_ativa_id' => $igreja->id])
            ->get(route('coordenador.dashboard'))
            ->assertOk()
            ->assertSee(route('coordenador.tempos-liturgicos.index'), false)
            ->assertSee(route('coordenador.momentos-liturgicos.index'), false);

        $this
            ->actingAs($musico)
            ->withSession(['igreja_ativa_id' => $igreja->id])
            ->get(route('member.dashboard'))
            ->assertOk()
            ->assertDontSee(route('coordenador.tempos-liturgicos.index'), false)
            ->assertDontSee(route('coordenador.momentos-liturgicos.index'), false)
            ->assertDontSee(route('admin.tempos-liturgicos.index'), false)
            ->assertDontSee(route('admin.momentos-liturgicos.index'), false);
    }

    public function test_inativar_classificacao_liturgica_preserva_registro_no_banco(): void
    {
        /** @var Usuario $adminMaster */
        $adminMaster = Usuario::factory()->adminMaster()->create();
        /** @var TempoLiturgico $tempo */
        $tempo = TempoLiturgico::query()->create([
            'nome' => 'Tempo para inativar',
            'ativo' => true,
        ]);

        $this
            ->actingAs($adminMaster)
            ->delete(route('admin.tempos-liturgicos.destroy', $tempo))
            ->assertRedirect(route('admin.tempos-liturgicos.index'));

        $this->assertDatabaseHas('classificacoes_liturgicas', [
            'id' => $tempo->id,
            'tipo' => 'tempo',
            'nome' => 'Tempo para inativar',
            'ativo' => false,
        ]);
    }
}
