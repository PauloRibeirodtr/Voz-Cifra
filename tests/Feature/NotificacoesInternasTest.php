<?php

namespace Tests\Feature;

use App\Enums\PapelIgreja;
use App\Models\Igreja;
use App\Models\Usuario;
use App\Services\GestaoUsuariosIgrejaService;
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

    public function test_notificacao_com_destino_indisponivel_redireciona_para_rota_segura(): void
    {
        $igreja = Igreja::factory()->create();
        $usuario = Usuario::factory()->create([
            'primeiro_acesso' => false,
        ]);
        $usuario->adicionarPapel(PapelIgreja::ADMIN_LOCAL, $igreja);

        $notificacao = $usuario->notificacoesInternas()->create([
            'tipo' => 'pedido_mudanca_tom',
            'titulo' => 'Pedido antigo',
            'mensagem' => 'Este item ja nao existe.',
            'url' => '/igreja/missas/999999#repertorio-item-888888',
        ]);

        $this
            ->actingAs($usuario->fresh())
            ->post(route('notificacoes.ler', $notificacao))
            ->assertRedirect(route('local-admin.dashboard'))
            ->assertSessionHas('warning');

        $this->assertNotNull($notificacao->fresh()->lida_em);
    }

    public function test_alteracao_de_status_gera_notificacao_interna_direcional(): void
    {
        Mail::fake();

        $adminMaster = Usuario::factory()->adminMaster()->create();
        $usuario = Usuario::factory()->create([
            'ativo' => true,
        ]);

        app(GestaoUsuariosIgrejaService::class)->alterarStatusConta(
            usuario: $usuario,
            ativo: false,
            ator: $adminMaster,
            contexto: ['origem' => 'teste']
        );

        $this->assertDatabaseHas('notificacoes_internas', [
            'usuario_id' => $usuario->id,
            'ator_id' => $adminMaster->id,
            'tipo' => 'conta_inativada',
            'titulo' => 'Conta inativada',
        ]);
    }

    public function test_musico_pode_desligar_avisos_gerais_por_email_no_perfil(): void
    {
        $igreja = Igreja::factory()->create();
        $usuario = Usuario::factory()->create([
            'email' => 'preferencias@example.com',
            'receber_notificacoes_email' => true,
        ]);
        $usuario->adicionarPapel(PapelIgreja::MUSICO, $igreja);

        $this
            ->actingAs($usuario)
            ->put(route('member.profile.update'), [
                'email' => 'preferencias@example.com',
                'telefone' => '(67) 99999-9999',
                'theme_preference' => 'dark',
                'receber_notificacoes_email' => '0',
                'password' => '',
                'password_confirmation' => '',
            ])
            ->assertRedirect(route('member.dashboard'));

        $usuario->refresh();

        $this->assertFalse($usuario->receber_notificacoes_email);
        $this->assertSame('dark', $usuario->theme_preference);
    }

    public function test_admin_master_pode_alterar_preferencias_na_central_de_configuracoes(): void
    {
        $adminMaster = Usuario::factory()->adminMaster()->create([
            'theme_preference' => 'system',
            'receber_notificacoes_email' => true,
            'primeiro_acesso' => false,
        ]);

        $this
            ->actingAs($adminMaster)
            ->put(route('admin.settings.preferences.update'), [
                'theme_preference' => 'dark',
                'receber_notificacoes_email' => '0',
            ])
            ->assertRedirect();

        $adminMaster->refresh();

        $this->assertSame('dark', $adminMaster->theme_preference);
        $this->assertFalse($adminMaster->receber_notificacoes_email);
        $this->assertDatabaseHas('auditoria_eventos', [
            'evento' => 'preferencias_atualizadas',
            'ator_id' => $adminMaster->id,
            'alvo_id' => $adminMaster->id,
        ]);
    }
}
