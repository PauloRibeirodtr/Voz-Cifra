<?php

// cSpell:ignore Papel

namespace Tests\Feature\Security;

use App\Enums\PapelIgreja;
use App\Models\Chamado;
use App\Models\Igreja;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChamadosAcessoTest extends TestCase
{
    use RefreshDatabase;

    public function test_musico_ve_apenas_os_proprios_chamados(): void
    {
        /** @var Igreja $igreja */
        $igreja = Igreja::factory()->create();
        /** @var Usuario $musico */
        $musico = Usuario::factory()->create();
        /** @var Usuario $outroMusico */
        $outroMusico = Usuario::factory()->create();
        $musico->adicionarPapel(PapelIgreja::MUSICO, $igreja);
        $outroMusico->adicionarPapel(PapelIgreja::MUSICO, $igreja);

        $chamadoDoMusico = $this->criarChamado($musico, 'Meu chamado visivel');
        $chamadoDeOutraPessoa = $this->criarChamado($outroMusico, 'Chamado de outra pessoa');

        $this
            ->actingAs($musico)
            ->withSession(['igreja_ativa_id' => $igreja->id])
            ->get(route('member.chamados.index'))
            ->assertOk()
            ->assertSee($chamadoDoMusico->titulo)
            ->assertDontSee($chamadoDeOutraPessoa->titulo);

        $this
            ->actingAs($musico)
            ->withSession(['igreja_ativa_id' => $igreja->id])
            ->get(route('member.chamados.show', $chamadoDeOutraPessoa))
            ->assertForbidden();
    }

    public function test_admin_local_em_area_member_tambem_ve_apenas_os_proprios_chamados(): void
    {
        /** @var Igreja $igreja */
        $igreja = Igreja::factory()->create();
        /** @var Usuario $adminLocal */
        $adminLocal = Usuario::factory()->create();
        /** @var Usuario $outroAdmin */
        $outroAdmin = Usuario::factory()->create();
        $adminLocal->adicionarPapel(PapelIgreja::ADMIN_LOCAL, $igreja);
        $outroAdmin->adicionarPapel(PapelIgreja::ADMIN_LOCAL, $igreja);

        $chamadoDoAdmin = $this->criarChamado($adminLocal, 'Pedido do admin local');
        $chamadoDeOutraPessoa = $this->criarChamado($outroAdmin, 'Pedido de outro admin');

        $this
            ->actingAs($adminLocal)
            ->withSession(['igreja_ativa_id' => $igreja->id])
            ->get(route('member.chamados.index'))
            ->assertOk()
            ->assertSee($chamadoDoAdmin->titulo)
            ->assertDontSee($chamadoDeOutraPessoa->titulo);

        $this
            ->actingAs($adminLocal)
            ->withSession(['igreja_ativa_id' => $igreja->id])
            ->get(route('member.chamados.show', $chamadoDeOutraPessoa))
            ->assertForbidden();
    }

    public function test_admin_master_e_coordenador_podem_ver_fila_de_chamados(): void
    {
        /** @var Igreja $igreja */
        $igreja = Igreja::factory()->create();
        /** @var Usuario $adminMaster */
        $adminMaster = Usuario::factory()->adminMaster()->create();
        /** @var Usuario $coordenador */
        $coordenador = Usuario::factory()->create();
        $coordenador->adicionarPapel(PapelIgreja::COORDENADOR, $igreja);
        /** @var Usuario $solicitante */
        $solicitante = Usuario::factory()->create();
        $solicitante->adicionarPapel(PapelIgreja::MUSICO, $igreja);

        $chamado = $this->criarChamado($solicitante, 'Chamado para atendimento');

        $this
            ->actingAs($adminMaster)
            ->get(route('admin.chamados.index'))
            ->assertOk()
            ->assertSee($chamado->titulo);

        $this
            ->actingAs($coordenador)
            ->withSession(['igreja_ativa_id' => $igreja->id])
            ->get(route('coordenador.chamados.index'))
            ->assertOk()
            ->assertSee($chamado->titulo);
    }

    private function criarChamado(Usuario $solicitante, string $titulo): Chamado
    {
        return Chamado::query()->create([
            'protocolo' => 'SUP-' . str_pad((string) (Chamado::query()->count() + 1), 6, '0', STR_PAD_LEFT),
            'titulo' => $titulo,
            'descricao' => 'Descricao do chamado com detalhes suficientes.',
            'status' => 'aberto',
            'prioridade' => 'media',
            'categoria' => 'outro',
            'canal_origem' => 'teste',
            'solicitante_usuario_id' => $solicitante->id,
            'solicitante_nome' => $solicitante->nome,
            'solicitante_email' => $solicitante->email,
            'ultima_interacao_em' => now(),
        ]);
    }
}
