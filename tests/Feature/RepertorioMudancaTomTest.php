<?php

namespace Tests\Feature;

use App\Enums\PapelIgreja;
use App\Models\Igreja;
use App\Models\Missa;
use App\Models\MissaMusica;
use App\Models\Musica;
use App\Models\SolicitacaoMudancaTom;
use App\Models\Usuario;
use App\Models\VersaoMusical;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RepertorioMudancaTomTest extends TestCase
{
    use RefreshDatabase;

    public function test_musico_solicita_mudanca_de_tom_e_admin_recebe_notificacao(): void
    {
        [$igreja, $adminLocal, $musico, $item] = $this->montarRepertorio();
        $adminMaster = Usuario::factory()->create([
            'perfil_global' => 'admin_master',
            'nivel_global' => 6,
            'primeiro_acesso' => false,
        ]);

        $this
            ->actingAs($musico)
            ->post(route('member.repertorio.tom.solicitar', $item), [
                'tom_sugerido' => 'G',
                'observacao' => 'Fica melhor para as vozes.',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('solicitacoes_mudanca_tom', [
            'missa_musica_id' => $item->id,
            'igreja_id' => $igreja->id,
            'usuario_id' => $musico->id,
            'tom_atual' => 'D',
            'tom_sugerido' => 'G',
            'status' => SolicitacaoMudancaTom::STATUS_PENDENTE,
        ]);

        $this->assertDatabaseHas('notificacoes_internas', [
            'usuario_id' => $adminLocal->id,
            'ator_id' => $musico->id,
            'igreja_id' => $igreja->id,
            'tipo' => 'pedido_mudanca_tom',
        ]);
        $this->assertDatabaseHas('notificacoes_internas', [
            'usuario_id' => $adminMaster->id,
            'ator_id' => $musico->id,
            'igreja_id' => $igreja->id,
            'tipo' => 'pedido_mudanca_tom',
        ]);
    }

    public function test_usuario_com_papel_de_admin_recebe_o_proprio_pedido_para_revisar(): void
    {
        [$igreja, , $usuario, $item] = $this->montarRepertorio();
        $usuario->adicionarPapel(PapelIgreja::ADMIN_LOCAL, $igreja);

        $this
            ->actingAs($usuario->fresh())
            ->post(route('member.repertorio.tom.solicitar', $item), [
                'tom_sugerido' => 'G',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('notificacoes_internas', [
            'usuario_id' => $usuario->id,
            'ator_id' => $usuario->id,
            'igreja_id' => $igreja->id,
            'tipo' => 'pedido_mudanca_tom',
        ]);
    }

    public function test_admin_aprova_pedido_e_tom_do_repertorio_e_atualizado(): void
    {
        [, $adminLocal, $musico, $item] = $this->montarRepertorio();

        $solicitacao = SolicitacaoMudancaTom::query()->create([
            'missa_musica_id' => $item->id,
            'missa_id' => $item->missa_id,
            'igreja_id' => $item->missa->igreja_id,
            'usuario_id' => $musico->id,
            'tom_atual' => 'D',
            'tom_sugerido' => 'G',
            'status' => SolicitacaoMudancaTom::STATUS_PENDENTE,
        ]);

        $this
            ->actingAs($adminLocal)
            ->post(route('local-admin.repertorio.tom.aprovar', $solicitacao))
            ->assertRedirect(route('local-admin.missas.show', $item->missa_id) . '#repertorio-item-' . $item->id);

        $this->assertSame('G', $item->fresh()->tom_usado);
        $this->assertSame(SolicitacaoMudancaTom::STATUS_APROVADA, $solicitacao->fresh()->status);
        $this->assertDatabaseHas('notificacoes_internas', [
            'usuario_id' => $musico->id,
            'ator_id' => $adminLocal->id,
            'tipo' => 'pedido_mudanca_tom_aprovado',
        ]);
    }

    private function montarRepertorio(): array
    {
        $igreja = Igreja::factory()->create();
        $adminLocal = Usuario::factory()->create([
            'primeiro_acesso' => false,
        ]);
        $musico = Usuario::factory()->create([
            'primeiro_acesso' => false,
        ]);
        $adminLocal->adicionarPapel(PapelIgreja::ADMIN_LOCAL, $igreja);
        $musico->adicionarPapel(PapelIgreja::MUSICO, $igreja);

        $musica = Musica::query()->create([
            'titulo' => 'Canto de Entrada',
            'artista' => 'Equipe',
            'letra' => 'Letra',
            'criado_por' => $adminLocal->id,
            'ativo' => true,
        ]);
        $versao = VersaoMusical::query()->create([
            'musica_id' => $musica->id,
            'titulo' => 'Principal',
            'tom_musical' => 'D',
            'letra_com_cifras' => '[D]Letra',
            'criado_por' => $adminLocal->id,
            'ativo' => true,
        ]);
        $missa = Missa::query()->create([
            'igreja_id' => $igreja->id,
            'titulo' => 'Missa de Domingo',
            'data_missa' => now()->addDay()->toDateString(),
            'hora_inicio' => '19:00',
            'hora_fim' => '20:00',
            'publica_para_musicos' => true,
            'ativo' => true,
        ]);
        $item = MissaMusica::query()->create([
            'missa_id' => $missa->id,
            'musica_id' => $musica->id,
            'versao_musical_id' => $versao->id,
            'tom_usado' => null,
            'ordem' => 1,
        ]);

        return [$igreja, $adminLocal->fresh(), $musico->fresh(), $item->fresh(['missa'])];
    }
}
