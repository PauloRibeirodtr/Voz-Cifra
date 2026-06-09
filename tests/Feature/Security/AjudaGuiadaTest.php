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
            ->assertSee('O que voc&ecirc; quer fazer?', false)
            ->assertSeeText('Músico')
            ->assertSeeText('Ver repertório')
            ->assertSeeText('Abrir chamado de suporte')
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
            ->assertSeeText('Admin local')
            ->assertSeeText('Coordenador')
            ->assertSeeText('Músico')
            ->assertSeeText('Cadastrar músico')
            ->assertSeeText('Montar uma missa')
            ->assertSeeText('Cadastrar música ou cifra')
            ->assertSeeText('Ver repertório');
    }

    public function test_admin_master_recebe_guia_visual_para_cadastrar_usuario(): void
    {
        /** @var Usuario $adminMaster */
        $adminMaster = Usuario::factory()->adminMaster()->create();

        $this
            ->actingAs($adminMaster)
            ->get(route('admin.usuarios.create'))
            ->assertOk()
            ->assertSeeText('Cadastrar usuário')
            ->assertSee('data-guide-id="cadastro-usuario"', false)
            ->assertSee('data-guide-target="usuario-tipo"', false)
            ->assertSeeText('Escolha o perfil permitido')
            ->assertSee('data-guide-target="usuario-telefone"', false)
            ->assertSeeText('Conclua o cadastro');
    }

    public function test_admin_local_recebe_guia_de_cadastro_de_musico(): void
    {
        /** @var Igreja $igreja */
        $igreja = Igreja::factory()->create();
        /** @var Usuario $adminLocal */
        $adminLocal = Usuario::factory()->create();
        $adminLocal->adicionarPapel(PapelIgreja::ADMIN_LOCAL, $igreja);

        $this
            ->actingAs($adminLocal)
            ->withSession(['igreja_ativa_id' => $igreja->id])
            ->get(route('local-admin.musicos.create'))
            ->assertOk()
            ->assertSee('data-guide-id="cadastro-musico-local"', false)
            ->assertSee('data-guide-target="musico-nome"', false)
            ->assertSeeText('Admin local cadastra apenas músicos da igreja ativa');
    }

    public function test_coordenador_recebe_acao_e_guia_para_cadastrar_admin_local(): void
    {
        /** @var Igreja $igreja */
        $igreja = Igreja::factory()->create();
        /** @var Usuario $coordenador */
        $coordenador = Usuario::factory()->create();
        $coordenador->adicionarPapel(PapelIgreja::COORDENADOR, $igreja);

        $this
            ->actingAs($coordenador)
            ->withSession(['igreja_ativa_id' => $igreja->id])
            ->get(route('coordenador.dashboard'))
            ->assertOk()
            ->assertSeeText('Cadastrar admin local')
            ->assertSee('data-guide-id="cadastro-admin-local-coordenador"', false)
            ->assertSee('data-guide-target="admin-local-form"', false)
            ->assertSee(route('coordenador.igreja.admins-locais.store'), false);
    }
}
