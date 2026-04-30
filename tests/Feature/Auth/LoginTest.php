<?php

namespace Tests\Feature\Auth;

use App\Enums\PapelIgreja;
use App\Models\Igreja;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_usuario_musico_ativo_consegue_login_com_email_e_senha_manual(): void
    {
        $igreja = Igreja::factory()->create();
        $usuario = Usuario::factory()->create([
            'email' => 'Musico.Login@Example.com',
            'password' => 'SenhaManual123!',
            'ativo' => true,
            'primeiro_acesso' => false,
        ]);
        $usuario->adicionarPapel(PapelIgreja::MUSICO, $igreja);

        $this
            ->post(route('login.attempt'), [
                'email' => 'musico.login@example.com',
                'password' => 'SenhaManual123!',
            ])
            ->assertRedirect(route('member.dashboard'));

        $this->assertAuthenticatedAs($usuario);
    }

    public function test_usuario_musico_consegue_login_com_cpf_e_senha_provisoria_sem_pontuacao(): void
    {
        $igreja = Igreja::factory()->create();
        $usuario = Usuario::factory()->create([
            'cpf' => '123.456.789-01',
            'password' => '12345678901',
            'ativo' => true,
            'primeiro_acesso' => true,
        ]);
        $usuario->adicionarPapel(PapelIgreja::MUSICO, $igreja);

        $this
            ->post(route('login.attempt'), [
                'email' => '12345678901',
                'password' => '12345678901',
            ])
            ->assertRedirect(route('member.profile'));

        $this->assertAuthenticatedAs($usuario);
    }

    public function test_usuario_apenas_musico_em_primeiro_acesso_consegue_abrir_perfil(): void
    {
        $igreja = Igreja::factory()->create();
        $usuario = Usuario::factory()->create([
            'password' => '12345678901',
            'ativo' => true,
            'primeiro_acesso' => true,
        ]);
        $usuario->adicionarPapel(PapelIgreja::MUSICO, $igreja);

        $this
            ->actingAs($usuario)
            ->get(route('member.profile'))
            ->assertOk()
            ->assertSee('Meu perfil')
            ->assertSee('Repert')
            ->assertDontSee('Administra&ccedil;&atilde;o central', false)
            ->assertDontSee('>Igrejas<', false)
            ->assertDontSee('>Usu&aacute;rios<', false);
    }

    public function test_cadastro_de_musico_sem_senha_define_cpf_sem_pontuacao_como_senha_provisoria(): void
    {
        Mail::fake();

        $adminMaster = Usuario::factory()->adminMaster()->create([
            'primeiro_acesso' => false,
        ]);
        $igreja = Igreja::factory()->create();

        $this
            ->actingAs($adminMaster)
            ->post(route('admin.usuarios.store'), [
                'tipo_cadastro' => 'musico',
                'igreja_id' => $igreja->id,
                'nome' => 'Musico Novo',
                'cpf' => '987.654.321-00',
                'email' => 'MUSICO.NOVO@EXAMPLE.COM',
                'telefone' => '(67) 99999-0000',
                'password' => '',
                'password_confirmation' => '',
                'ativo' => '1',
            ])
            ->assertSessionHasNoErrors();

        $usuario = Usuario::query()->where('cpf', '987.654.321-00')->firstOrFail();

        $this->assertSame('musico.novo@example.com', $usuario->email);
        $this->assertTrue($usuario->primeiro_acesso);
        $this->assertTrue($usuario->temPapelNaIgreja(PapelIgreja::MUSICO, $igreja->id));

        Auth::logout();

        $this
            ->post(route('login.attempt'), [
                'email' => 'musico.novo@example.com',
                'password' => '98765432100',
            ])
            ->assertRedirect(route('member.profile'));

        $this->assertAuthenticatedAs($usuario);
    }

    public function test_usuario_inativo_recebe_mensagem_clara_no_login(): void
    {
        $usuario = Usuario::factory()->create([
            'email' => 'inativo@example.com',
            'password' => 'SenhaManual123!',
            'ativo' => false,
        ]);

        $this
            ->post(route('login.attempt'), [
                'email' => $usuario->email,
                'password' => 'SenhaManual123!',
            ])
            ->assertSessionHasErrors([
                'email' => 'Sua conta esta inativa. Fale com o administrador da igreja para reativar o acesso.',
            ]);

        $this->assertGuest();
    }

    public function test_usuario_sem_vinculo_recebe_mensagem_clara_apos_senha_valida(): void
    {
        $usuario = Usuario::factory()->create([
            'email' => 'sem.vinculo@example.com',
            'password' => 'SenhaManual123!',
            'ativo' => true,
            'primeiro_acesso' => false,
        ]);

        $this
            ->post(route('login.attempt'), [
                'email' => $usuario->email,
                'password' => 'SenhaManual123!',
            ])
            ->assertSessionHasErrors([
                'email' => 'Sua conta ainda nao possui vinculo operacional ativo. Solicite ao administrador que vincule seu usuario a uma igreja e conceda o papel correto.',
            ]);

        $this->assertGuest();
    }
}
