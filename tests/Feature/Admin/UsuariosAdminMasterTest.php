<?php

namespace Tests\Feature\Admin;

use App\Enums\PapelIgreja;
use App\Mail\ConviteAcessoInicialMail;
use App\Models\Igreja;
use App\Models\HistoricoEnvioEmail;
use App\Models\Usuario;
use Database\Seeders\AdminMasterSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class UsuariosAdminMasterTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_master_filtra_igrejas_por_nome_ou_cidade(): void
    {
        $adminMaster = Usuario::factory()->adminMaster()->create([
            'primeiro_acesso' => false,
        ]);

        Igreja::factory()->create([
            'nome' => 'Paroquia Sagrado Coracao',
            'cidade' => 'Ladario',
        ]);

        Igreja::factory()->create([
            'nome' => 'Capela Nossa Senhora',
            'cidade' => 'Corumba',
        ]);

        Igreja::factory()->create([
            'nome' => 'Igreja Santo Expedito',
            'cidade' => 'Guatambu',
        ]);

        $this
            ->actingAs($adminMaster)
            ->get(route('admin.igrejas.index', ['busca' => 'Corumba']))
            ->assertOk()
            ->assertSee('Capela Nossa Senhora')
            ->assertDontSee('Paroquia Sagrado Coracao')
            ->assertDontSee('Igreja Santo Expedito')
            ->assertSee('Exibindo resultados para')
            ->assertSee('Limpar');

        $this
            ->actingAs($adminMaster)
            ->get(route('admin.igrejas.index', ['busca' => 'Sagrado']))
            ->assertOk()
            ->assertSee('Paroquia Sagrado Coracao')
            ->assertDontSee('Capela Nossa Senhora')
            ->assertDontSee('Igreja Santo Expedito');
    }

    public function test_admin_master_filtra_igrejas_sem_diferenciar_acento_e_por_status(): void
    {
        $adminMaster = Usuario::factory()->adminMaster()->create([
            'primeiro_acesso' => false,
        ]);

        Igreja::factory()->create([
            'nome' => 'Paróquia Nossa Senhora dos Remédios',
            'cidade' => 'Ladário',
            'status_operacional' => 'operacional',
        ]);

        Igreja::factory()->create([
            'nome' => 'Igreja Santo Expedito',
            'cidade' => 'Guatambu',
            'status_operacional' => 'aguardando_admin_local',
        ]);

        $this
            ->actingAs($adminMaster)
            ->get(route('admin.igrejas.index', ['busca' => 'remedios']))
            ->assertOk()
            ->assertSee('Paróquia Nossa Senhora dos Remédios')
            ->assertDontSee('Igreja Santo Expedito');

        $this
            ->actingAs($adminMaster)
            ->get(route('admin.igrejas.index', ['status' => 'aguardando']))
            ->assertOk()
            ->assertSee('Igreja Santo Expedito')
            ->assertDontSee('Paróquia Nossa Senhora dos Remédios');
    }

    public function test_edicao_da_igreja_foca_em_dados_e_usuarios_vinculados(): void
    {
        $adminMaster = Usuario::factory()->adminMaster()->create([
            'primeiro_acesso' => false,
        ]);
        $igreja = Igreja::factory()->create([
            'nome' => 'Paroquia Sao Joao Bosco',
        ]);

        $this
            ->actingAs($adminMaster)
            ->get(route('admin.igrejas.edit', $igreja))
            ->assertOk()
            ->assertSee('Dados da igreja')
            ->assertSee('Usuários vinculados a esta igreja')
            ->assertSee('Cadastrar usuário nesta igreja')
            ->assertDontSee('Cadastrar ou atualizar admin local agora')
            ->assertDontSee('Adicionar ou promover admin local')
            ->assertDontSee('Coordenadores da igreja');
    }

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

    public function test_admin_master_nao_pode_resetar_senha_de_outro_admin_master(): void
    {
        $ator = Usuario::factory()->adminMaster()->create([
            'primeiro_acesso' => false,
        ]);
        $alvo = Usuario::factory()->adminMaster()->create([
            'primeiro_acesso' => false,
            'email' => 'outro.master@example.com',
        ]);

        $this
            ->actingAs($ator)
            ->post(route('admin.usuarios.password.reset', $alvo))
            ->assertForbidden();
    }

    public function test_admin_master_nao_pode_inativar_outro_admin_master(): void
    {
        $ator = Usuario::factory()->adminMaster()->create([
            'primeiro_acesso' => false,
        ]);
        $alvo = Usuario::factory()->adminMaster()->create([
            'primeiro_acesso' => false,
            'email' => 'master.status@example.com',
        ]);

        $this
            ->actingAs($ator)
            ->post(route('admin.usuarios.toggle', $alvo))
            ->assertForbidden();
    }

    public function test_igreja_sem_admin_local_fica_aguardando_admin_local(): void
    {
        $adminMaster = Usuario::factory()->adminMaster()->create([
            'primeiro_acesso' => false,
        ]);

        $this
            ->actingAs($adminMaster)
            ->post(route('admin.igrejas.store'), [
                'nome' => 'Paroquia Sao Miguel',
                'cnpj' => '12.345.678/0001-90',
                'cidade' => 'Corumba',
                'estado' => 'MS',
                'ativo' => '1',
                'criar_admin_local_agora' => 'nao',
            ])
            ->assertSessionHasNoErrors();

        $igreja = Igreja::query()->where('nome', 'Paroquia Sao Miguel')->first();

        $this->assertNotNull($igreja);
        $this->assertSame('aguardando_admin_local', $igreja->status_operacional);
    }

    public function test_igreja_com_admin_local_ja_nasce_operacional(): void
    {
        Mail::fake();

        $adminMaster = Usuario::factory()->adminMaster()->create([
            'primeiro_acesso' => false,
        ]);

        $this
            ->actingAs($adminMaster)
            ->post(route('admin.igrejas.store'), [
                'nome' => 'Paroquia Nossa Senhora',
                'cnpj' => '98.765.432/0001-10',
                'cidade' => 'Ladario',
                'estado' => 'MS',
                'ativo' => '1',
                'criar_admin_local_agora' => 'sim',
                'admin_nome' => 'Maria Coordenadora',
                'admin_cpf' => '456.789.123-00',
                'admin_email' => 'maria.admin@example.com',
                'admin_telefone' => '(67) 99999-2222',
            ])
            ->assertSessionHasNoErrors();

        $igreja = Igreja::query()->where('nome', 'Paroquia Nossa Senhora')->first();
        $adminLocal = Usuario::query()->where('email', 'maria.admin@example.com')->first();

        $this->assertNotNull($igreja);
        $this->assertNotNull($adminLocal);
        $this->assertSame('operacional', $igreja->fresh()->status_operacional);
        $this->assertTrue($adminLocal->temPapelNaIgreja(PapelIgreja::ADMIN_LOCAL, $igreja->id));
    }

    public function test_atualizacao_da_igreja_sem_admin_local_agora_permanece_valida(): void
    {
        $adminMaster = Usuario::factory()->adminMaster()->create([
            'primeiro_acesso' => false,
        ]);
        $igreja = Igreja::factory()->create([
            'status_operacional' => 'aguardando_admin_local',
        ]);

        $this
            ->actingAs($adminMaster)
            ->put(route('admin.igrejas.update', $igreja), [
                'nome' => 'Paroquia Atualizada',
                'cnpj' => $igreja->cnpj,
                'cep' => $igreja->cep,
                'endereco' => $igreja->endereco,
                'cidade' => $igreja->cidade,
                'estado' => $igreja->estado,
                'ativo' => '1',
                'criar_admin_local_agora' => 'nao',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('admin.igrejas.index'));

        $igreja->refresh();

        $this->assertSame('Paroquia Atualizada', $igreja->nome);
        $this->assertSame('aguardando_admin_local', $igreja->status_operacional);
    }

    public function test_admin_master_pode_se_autovincular_a_papeis_operacionais_no_proprio_perfil(): void
    {
        $adminMaster = Usuario::factory()->adminMaster()->create([
            'primeiro_acesso' => false,
        ]);
        $igreja = Igreja::factory()->create();

        $this
            ->actingAs($adminMaster)
            ->post(route('admin.profile.vinculos.store'), [
                'igreja_id' => $igreja->id,
                'papeis' => [
                    PapelIgreja::ADMIN_LOCAL->value,
                    PapelIgreja::COORDENADOR->value,
                ],
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('admin.profile'));

        $adminMaster->refresh();

        $this->assertTrue($adminMaster->ehAdminMaster());
        $this->assertTrue($adminMaster->temPapelNaIgreja(PapelIgreja::ADMIN_LOCAL, $igreja->id));
        $this->assertTrue($adminMaster->temPapelNaIgreja(PapelIgreja::COORDENADOR, $igreja->id));
    }

    public function test_admin_master_seeder_usa_senha_padrao_e_primeiro_acesso_ativo(): void
    {
        $this->seed(AdminMasterSeeder::class);

        $adminMaster = Usuario::query()
            ->where('perfil_global', 'admin_master')
            ->first();

        $this->assertNotNull($adminMaster);
        $this->assertTrue((bool) $adminMaster->primeiro_acesso);
        $this->assertTrue(Hash::check('admin123456', (string) $adminMaster->password));
    }
}
