<?php

namespace Tests\Feature\Security;

use App\Enums\PapelIgreja;
use App\Models\Igreja;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AjudaGuiadaTest extends TestCase
{
    use RefreshDatabase;

    public function test_musico_recebe_apenas_acoes_do_musico(): void
    {
        /** @var Igreja $igreja */
        $igreja = Igreja::factory()->create();
        /** @var Usuario $musico */
        $musico = Usuario::factory()->create();
        $musico->adicionarPapel(PapelIgreja::MUSICO, $igreja);

        $this
            ->actingAs($musico)
            ->withSession(['igreja_ativa_id' => $igreja->id])
            ->get(route('member.dashboard'))
            ->assertOk()
            ->assertSee('O que voce quer fazer?')
            ->assertSee('Musico')
            ->assertSee('Ver repertorio')
            ->assertSee('Abrir chamado de suporte')
            ->assertDontSee('Admin master');
    }

    public function test_acoes_aparecem_de_forma_cumulativa_por_perfil(): void
    {
        /** @var Igreja $igreja */
        $igreja = Igreja::factory()->create();
        /** @var Usuario $usuario */
        $usuario = Usuario::factory()->create();
        $usuario->adicionarPapel(PapelIgreja::ADMIN_LOCAL, $igreja);
        $usuario->adicionarPapel(PapelIgreja::COORDENADOR, $igreja);
        $usuario->adicionarPapel(PapelIgreja::MUSICO, $igreja);

        $this
            ->actingAs($usuario)
            ->withSession(['igreja_ativa_id' => $igreja->id])
            ->get(route('local-admin.dashboard'))
            ->assertOk()
            ->assertSee('Admin local')
            ->assertSee('Coordenador')
            ->assertSee('Musico')
            ->assertSee('Cadastrar musico')
            ->assertSee('Montar uma missa')
            ->assertSee('Cadastrar musica ou cifra')
            ->assertSee('Ver repertorio');
    }

    public function test_admin_master_recebe_guia_visual_para_cadastrar_usuario(): void
    {
        /** @var Usuario $adminMaster */
        $adminMaster = Usuario::factory()->adminMaster()->create();

        $this
            ->actingAs($adminMaster)
            ->get(route('admin.usuarios.create'))
            ->assertOk()
            ->assertSee('Cadastrar usuario')
            ->assertSee('data-guide-id="cadastro-usuario"', false)
            ->assertSee('data-guide-target="usuario-tipo"', false)
            ->assertSee('Escolha o tipo inicial')
            ->assertSee('Salve o cadastro');
    }
}
