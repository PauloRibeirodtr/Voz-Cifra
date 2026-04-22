<?php

namespace Tests\Feature\Admin;

use App\Enums\PapelIgreja;
use App\Mail\ConviteAcessoInicialMail;
use App\Models\Igreja;
use App\Models\HistoricoEnvioEmail;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class UsuariosAdminMasterTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_master_pode_criar_admin_local_sem_vinculo_inicial(): void
    {
        Mail::fake();

        $adminMaster = Usuario::factory()->adminMaster()->create([
            'primeiro_acesso' => false,
        ]);

        $response = $this
            ->actingAs($adminMaster)
            ->post(route('admin.usuarios.store'), [
                'tipo_cadastro' => 'admin_local',
                'nome' => 'Admin Local Novo',
                'cpf' => '123.456.789-01',
                'email' => 'admin.local@example.com',
                'telefone' => '(67) 99999-1111',
                'password' => 'SenhaForte123!',
                'password_confirmation' => 'SenhaForte123!',
                'ativo' => '1',
            ]);

        $usuario = Usuario::query()->where('email', 'admin.local@example.com')->first();

        $this->assertNotNull($usuario);
        $response->assertRedirect(route('admin.usuarios.edit', $usuario));
        $this->assertSame('usuario', $usuario->perfil_global);
        $this->assertTrue($usuario->ativo);
        $this->assertCount(0, $usuario->vinculosIgrejaAtivos()->get());
        Mail::assertSent(ConviteAcessoInicialMail::class, fn (ConviteAcessoInicialMail $mail) => $mail->alvo->is($usuario));
        $this->assertDatabaseHas('historico_envios_email', [
            'usuario_id' => $usuario->id,
            'tipo_email' => 'convite_acesso_inicial',
        ]);
    }

    public function test_admin_master_pode_criar_padre_sem_email_e_sem_login_publico(): void
    {
        Mail::fake();

        $adminMaster = Usuario::factory()->adminMaster()->create([
            'primeiro_acesso' => false,
        ]);

        $this
            ->actingAs($adminMaster)
            ->post(route('admin.usuarios.store'), [
                'tipo_cadastro' => 'padre',
                'nome' => 'Pe. Jose',
                'cpf' => '222.333.444-55',
                'email' => '',
                'ativo' => '1',
            ])
            ->assertSessionHasNoErrors();

        $padre = Usuario::query()->where('cpf', '222.333.444-55')->first();

        $this->assertNotNull($padre);
        $this->assertTrue($padre->eh_padre);
        $this->assertStringEndsWith('@sem-login.local', (string) $padre->email);
        $this->assertFalse($padre->primeiro_acesso);
        Mail::assertNothingSent();
        $this->assertFalse(HistoricoEnvioEmail::query()->where('usuario_id', $padre->id)->where('tipo_email', 'convite_acesso_inicial')->exists());
    }

    public function test_admin_master_pode_promover_padre_existente_sem_duplicar_usuario(): void
    {
        $adminMaster = Usuario::factory()->adminMaster()->create([
            'primeiro_acesso' => false,
        ]);
        $igreja = Igreja::factory()->create();
        $padre = Usuario::factory()->padre()->create([
            'cpf' => '333.444.555-66',
            'email' => 'celebrante.33344455566@sem-login.local',
            'primeiro_acesso' => false,
        ]);

        $this
            ->actingAs($adminMaster)
            ->post(route('admin.usuarios.vinculos.store', $padre), [
                'igreja_id' => $igreja->id,
                'papeis' => [PapelIgreja::MUSICO->value],
            ])
            ->assertSessionHasNoErrors();

        $padre->refresh();

        $this->assertTrue($padre->eh_padre);
        $this->assertSame(1, Usuario::query()->where('cpf', '333.444.555-66')->count());
        $this->assertTrue($padre->temPapelNaIgreja(PapelIgreja::MUSICO, $igreja->id));
    }

    public function test_admin_master_em_primeiro_acesso_precisa_trocar_senha_com_regra_forte(): void
    {
        $adminMaster = Usuario::factory()->adminMaster()->create([
            'primeiro_acesso' => true,
            'password' => 'SenhaInicial123!',
        ]);

        $this
            ->actingAs($adminMaster)
            ->put(route('admin.profile.update'), [
                'email' => $adminMaster->email,
                'telefone' => $adminMaster->telefone,
                'theme_preference' => 'system',
                'password' => '',
                'password_confirmation' => '',
            ])
            ->assertSessionHasErrors(['password']);

        $this
            ->actingAs($adminMaster->fresh())
            ->put(route('admin.profile.update'), [
                'email' => $adminMaster->email,
                'telefone' => $adminMaster->telefone,
                'theme_preference' => 'dark',
                'password' => 'NovaSenha123!',
                'password_confirmation' => 'NovaSenha123!',
            ])
            ->assertSessionHasNoErrors();

        $adminMaster->refresh();

        $this->assertFalse($adminMaster->primeiro_acesso);
    }
}
