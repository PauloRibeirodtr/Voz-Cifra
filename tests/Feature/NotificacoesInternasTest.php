<?php

namespace Tests\Feature;

use App\Enums\PapelIgreja;
use App\Models\Igreja;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class NotificacoesInternasTest extends TestCase
{
    use RefreshDatabase;

    public function test_usuario_recebe_notificacao_quando_papel_e_concedido_ou_removido(): void
    {
        Mail::fake();

        $adminMaster = Usuario::factory()->adminMaster()->create([
            'primeiro_acesso' => false,
        ]);
        $igreja = Igreja::factory()->create([
            'nome' => 'Paroquia Santa Cecilia',
        ]);
        $usuario = Usuario::factory()->create([
            'primeiro_acesso' => false,
        ]);

        $this
            ->actingAs($adminMaster)
            ->post(route('admin.usuarios.vinculos.store', $usuario), [
                'igreja_id' => $igreja->id,
                'papeis' => [PapelIgreja::MUSICO->value],
            ])
            ->assertRedirect(route('admin.usuarios.edit', $usuario));

        $this->assertDatabaseHas('notificacoes_internas', [
            'usuario_id' => $usuario->id,
            'ator_id' => $adminMaster->id,
            'igreja_id' => $igreja->id,
            'tipo' => 'papel_concedido',
            'titulo' => 'Novo acesso liberado',
            'lida_em' => null,
        ]);

        $this
            ->actingAs($adminMaster)
            ->post(route('admin.usuarios.vinculos.store', $usuario), [
                'igreja_id' => $igreja->id,
                'papeis' => [],
            ])
            ->assertRedirect(route('admin.usuarios.edit', $usuario));

        $this->assertDatabaseHas('notificacoes_internas', [
            'usuario_id' => $usuario->id,
            'ator_id' => $adminMaster->id,
            'igreja_id' => $igreja->id,
            'tipo' => 'papel_revogado',
            'titulo' => 'Acesso atualizado',
            'lida_em' => null,
        ]);
    }

    public function test_usuario_pode_marcar_notificacoes_como_lidas(): void
    {
        $usuario = Usuario::factory()->create([
            'primeiro_acesso' => false,
        ]);
        $notificacao = $usuario->notificacoesInternas()->create([
            'tipo' => 'aviso',
            'titulo' => 'Aviso importante',
            'mensagem' => 'Mensagem de teste.',
        ]);

        $this
            ->actingAs($usuario)
            ->post(route('notificacoes.ler', $notificacao))
            ->assertRedirect();

        $this->assertNotNull($notificacao->fresh()->lida_em);

        $outraNotificacao = $usuario->notificacoesInternas()->create([
            'tipo' => 'aviso',
            'titulo' => 'Outro aviso',
        ]);

        $this
            ->actingAs($usuario)
            ->post(route('notificacoes.ler-todas'))
            ->assertRedirect();

        $this->assertNotNull($outraNotificacao->fresh()->lida_em);
    }
}
