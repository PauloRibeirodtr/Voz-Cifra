<?php

// cSpell:ignore admin admins coordenador coordenado gestao igreja igrejas indevido musico papeis usuario

namespace Tests\Feature\Security;

use App\Enums\PapelIgreja;
use App\Mail\ConviteAcessoInicialMail;
use App\Models\Igreja;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class HierarquiaOperacionalTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_local_continua_podendo_criar_musico_apenas_na_igreja_ativa(): void
    {
        Mail::fake();

        /** @var Igreja $igreja */
        $igreja = Igreja::factory()->create();
        /** @var Igreja $outraIgreja */
        $outraIgreja = Igreja::factory()->create();
        /** @var Usuario $adminLocal */
        $adminLocal = Usuario::factory()->create();
        $adminLocal->adicionarPapel(PapelIgreja::ADMIN_LOCAL, $igreja);

        $this
            ->actingAs($adminLocal)
            ->withSession(['igreja_ativa_id' => $igreja->id])
            ->post(route('local-admin.musicos.store'), [
                'nome' => 'Musico Local',
                'cpf' => '12345678901',
                'email' => 'musico.local@example.com',
                'telefone' => '(67) 99999-0001',
                'ativo' => '1',
            ])
            ->assertRedirect(route('local-admin.musicos.index'));

        $musico = Usuario::query()->where('email', 'musico.local@example.com')->firstOrFail();

        $this->assertTrue($musico->temPapelNaIgreja(PapelIgreja::MUSICO, $igreja->id));
        $this->assertFalse($musico->temPapelNaIgreja(PapelIgreja::MUSICO, $outraIgreja->id));
        $this->assertSame('usuario', $musico->perfil_global);
        $this->assertSame(1, $musico->nivel_global);
        Mail::assertSent(ConviteAcessoInicialMail::class, fn (ConviteAcessoInicialMail $mail) => $mail->alvo->is($musico));
    }

    public function test_coordenador_continua_podendo_criar_musico_na_igreja_ativa(): void
    {
        Mail::fake();

        /** @var Igreja $igreja */
        $igreja = Igreja::factory()->create();
        /** @var Usuario $coordenador */
        $coordenador = Usuario::factory()->create();
        $coordenador->adicionarPapel(PapelIgreja::COORDENADOR, $igreja);

        $this
            ->actingAs($coordenador)
            ->withSession(['igreja_ativa_id' => $igreja->id])
            ->post(route('coordenador.musicos.store'), [
                'nome' => 'Musico Coordenado',
                'cpf' => '23456789012',
                'email' => 'musico.coordenado@example.com',
                'telefone' => '(67) 99999-0002',
                'ativo' => '1',
            ])
            ->assertRedirect(route('coordenador.musicos.index'));

        $musico = Usuario::query()->where('email', 'musico.coordenado@example.com')->firstOrFail();

        $this->assertTrue($musico->temPapelNaIgreja(PapelIgreja::MUSICO, $igreja->id));
        $this->assertFalse($musico->temPapelNaIgreja(PapelIgreja::COORDENADOR, $igreja->id));
        $this->assertFalse($musico->temPapelNaIgreja(PapelIgreja::ADMIN_LOCAL, $igreja->id));
    }

    public function test_coordenador_pode_atribuir_admin_local_somente_na_sua_igreja_ativa(): void
    {
        Mail::fake();

        /** @var Igreja $igrejaAtiva */
        $igrejaAtiva = Igreja::factory()->create();
        /** @var Igreja $outraIgreja */
        $outraIgreja = Igreja::factory()->create();
        /** @var Usuario $coordenador */
        $coordenador = Usuario::factory()->create();
        $coordenador->adicionarPapel(PapelIgreja::COORDENADOR, $igrejaAtiva);

        $this
            ->actingAs($coordenador)
            ->withSession(['igreja_ativa_id' => $igrejaAtiva->id])
            ->post(route('coordenador.igreja.admins-locais.store'), [
                'nome' => 'Admin Local Coordenado',
                'cpf' => '34567890123',
                'email' => 'admin.local.coordenado@example.com',
                'telefone' => '(67) 99999-0003',
            ])
            ->assertRedirect(route('coordenador.dashboard'));

        $adminLocal = Usuario::query()->where('email', 'admin.local.coordenado@example.com')->firstOrFail();

        $this->assertTrue($adminLocal->temPapelNaIgreja(PapelIgreja::ADMIN_LOCAL, $igrejaAtiva->id));
        $this->assertFalse($adminLocal->temPapelNaIgreja(PapelIgreja::ADMIN_LOCAL, $outraIgreja->id));
        $this->assertFalse($adminLocal->ehAdminMaster());
    }

    public function test_admin_local_nao_consegue_usar_rota_do_coordenador_para_criar_admin_local(): void
    {
        Mail::fake();

        /** @var Igreja $igreja */
        $igreja = Igreja::factory()->create();
        /** @var Usuario $adminLocal */
        $adminLocal = Usuario::factory()->create();
        $adminLocal->adicionarPapel(PapelIgreja::ADMIN_LOCAL, $igreja);

        $this
            ->actingAs($adminLocal)
            ->withSession(['igreja_ativa_id' => $igreja->id])
            ->post(route('coordenador.igreja.admins-locais.store'), [
                'nome' => 'Admin Local Indevido',
                'cpf' => '45678901234',
                'email' => 'admin.local.indevido@example.com',
                'telefone' => '(67) 99999-0004',
            ])
            ->assertRedirect(route('local-admin.dashboard'));

        $this->assertDatabaseMissing('usuarios', [
            'email' => 'admin.local.indevido@example.com',
        ]);
    }

    public function test_musico_nao_consegue_acessar_fluxos_de_gestao_operacional(): void
    {
        Mail::fake();

        /** @var Igreja $igreja */
        $igreja = Igreja::factory()->create();
        /** @var Usuario $musico */
        $musico = Usuario::factory()->create();
        $musico->adicionarPapel(PapelIgreja::MUSICO, $igreja);

        $this
            ->actingAs($musico)
            ->withSession(['igreja_ativa_id' => $igreja->id])
            ->post(route('local-admin.musicos.store'), [
                'nome' => 'Musico Indevido',
                'cpf' => '56789012345',
                'email' => 'musico.indevido@example.com',
                'telefone' => '(67) 99999-0005',
                'ativo' => '1',
            ])
            ->assertRedirect(route('member.dashboard'));

        $this->assertDatabaseMissing('usuarios', [
            'email' => 'musico.indevido@example.com',
        ]);
    }

    public function test_admin_local_nao_consegue_reaproveitar_admin_master_em_cadastro_de_musico(): void
    {
        Mail::fake();

        /** @var Igreja $igreja */
        $igreja = Igreja::factory()->create();
        /** @var Usuario $adminLocal */
        $adminLocal = Usuario::factory()->create();
        $adminLocal->adicionarPapel(PapelIgreja::ADMIN_LOCAL, $igreja);
        /** @var Usuario $adminMaster */
        $adminMaster = Usuario::factory()->adminMaster()->create([
            'nome' => 'Admin Master Original',
            'cpf' => '67890123456',
            'email' => 'admin.master.seguro@example.com',
        ]);

        $this
            ->actingAs($adminLocal)
            ->withSession(['igreja_ativa_id' => $igreja->id])
            ->post(route('local-admin.musicos.store'), [
                'nome' => 'Tentativa Indevida',
                'cpf' => $adminMaster->cpf,
                'email' => $adminMaster->email,
                'telefone' => '(67) 99999-0006',
                'ativo' => '1',
            ])
            ->assertSessionHasErrors('usuario');

        $adminMaster->refresh();

        $this->assertSame('Admin Master Original', $adminMaster->nome);
        $this->assertTrue($adminMaster->ehAdminMaster());
        $this->assertFalse($adminMaster->temPapelNaIgreja(PapelIgreja::MUSICO, $igreja->id));
    }

    public function test_coordenador_nao_consegue_vincular_admin_master_como_musico_existente(): void
    {
        Mail::fake();

        /** @var Igreja $igreja */
        $igreja = Igreja::factory()->create();
        /** @var Usuario $coordenador */
        $coordenador = Usuario::factory()->create();
        $coordenador->adicionarPapel(PapelIgreja::COORDENADOR, $igreja);
        /** @var Usuario $adminMaster */
        $adminMaster = Usuario::factory()->adminMaster()->create();

        $this
            ->actingAs($coordenador)
            ->withSession(['igreja_ativa_id' => $igreja->id])
            ->post(route('coordenador.musicos.vincular-existente'), [
                'usuario_id' => $adminMaster->id,
            ])
            ->assertSessionHasErrors('usuario');

        $adminMaster->refresh();

        $this->assertTrue($adminMaster->ehAdminMaster());
        $this->assertFalse($adminMaster->temPapelNaIgreja(PapelIgreja::MUSICO, $igreja->id));
    }
}
