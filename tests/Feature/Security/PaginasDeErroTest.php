<?php

// cSpell:ignore pagina publico usuario musico

namespace Tests\Feature\Security;

use App\Enums\PapelIgreja;
use App\Models\Igreja;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaginasDeErroTest extends TestCase
{
    use RefreshDatabase;

    public function test_404_para_visitante_oferece_apenas_retorno_publico(): void
    {
        $this
            ->get('/endereco-inexistente-para-visitante')
            ->assertNotFound()
            ->assertSee('Ir para a pagina publica')
            ->assertDontSee('Voltar')
            ->assertDontSee('Abrir meu painel');
    }

    public function test_404_para_usuario_logado_oferece_painel_do_proprio_perfil(): void
    {
        /** @var Igreja $igreja */
        $igreja = Igreja::factory()->create();
        /** @var Usuario $musico */
        $musico = Usuario::factory()->create();
        $musico->adicionarPapel(PapelIgreja::MUSICO, $igreja);

        $this
            ->actingAs($musico)
            ->withSession(['igreja_ativa_id' => $igreja->id])
            ->get('/endereco-inexistente-para-musico')
            ->assertNotFound()
            ->assertSee('Abrir meu painel')
            ->assertSee(route('member.dashboard'), false)
            ->assertDontSee('Voltar')
            ->assertDontSee('Ir para a pagina publica');
    }
}
