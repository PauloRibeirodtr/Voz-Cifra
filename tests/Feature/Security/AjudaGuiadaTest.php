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

    public function test_musico_recebe_tutorial_do_musico(): void
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
            ->assertSee('Ajuda guiada')
            ->assertSee('Musico')
            ->assertSee('Estudar e tocar')
            ->assertDontSee('Admin master');
    }

    public function test_tutoriais_aparecem_de_forma_cumulativa_por_perfil(): void
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
            ->assertSee('Cuidar da igreja')
            ->assertSee('Organizar repertorio')
            ->assertSee('Estudar e tocar');
    }
}
