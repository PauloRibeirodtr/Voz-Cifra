<?php

namespace Tests\Feature\Admin;

use App\Models\Igreja;
use App\Models\Musica;
use App\Models\Usuario;
use App\Models\VersaoMusical;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DuplicidadeInativacaoTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_recebe_alerta_antes_de_cadastrar_igreja_parecida(): void
    {
        $adminMaster = Usuario::factory()->adminMaster()->create([
            'primeiro_acesso' => false,
        ]);

        Igreja::factory()->create([
            'nome' => 'Paroquia Nossa Senhora dos Remedios',
            'cidade' => 'Ladario',
            'estado' => 'MS',
            'endereco' => 'Rua Cunha Couto, Centro',
        ]);

        $dados = [
            'nome' => 'Paroquia Nossa Senhora dos Remedios',
            'cnpj' => '03.030.921/0004-38',
            'cidade' => 'Ladario',
            'estado' => 'MS',
            'endereco' => 'Rua Cunha Couto',
            'ativo' => '1',
            'criar_admin_local_agora' => '0',
        ];

        $this
            ->actingAs($adminMaster)
            ->post(route('admin.igrejas.store'), $dados)
            ->assertRedirect()
            ->assertSessionHas('duplicidade_igreja');

        $this->assertSame(1, Igreja::query()->where('nome', 'Paroquia Nossa Senhora dos Remedios')->count());

        $this
            ->actingAs($adminMaster)
            ->post(route('admin.igrejas.store'), $dados + ['confirmar_duplicidade' => '1'])
            ->assertSessionHasNoErrors();

        $this->assertSame(2, Igreja::query()->where('nome', 'Paroquia Nossa Senhora dos Remedios')->count());
    }

    public function test_cards_admin_de_igreja_mostram_endereco_sem_destacar_cnpj(): void
    {
        $adminMaster = Usuario::factory()->adminMaster()->create([
            'primeiro_acesso' => false,
        ]);
        $igreja = Igreja::factory()->create([
            'nome' => 'Paroquia Sao Jose',
            'cnpj' => '11.222.333/0001-44',
            'endereco' => 'Rua Principal, 123',
            'cidade' => 'Ladario',
            'estado' => 'MS',
        ]);

        $this
            ->actingAs($adminMaster)
            ->get(route('admin.igrejas.index'))
            ->assertOk()
            ->assertSee($igreja->endereco)
            ->assertDontSee('CNPJ: ' . $igreja->cnpj);
    }

    public function test_admin_recebe_alerta_antes_de_cadastrar_musica_duplicada(): void
    {
        $adminMaster = Usuario::factory()->adminMaster()->create([
            'primeiro_acesso' => false,
        ]);

        Musica::query()->create([
            'titulo' => 'Vem Espirito Santo',
            'artista' => 'Ministerio Teste',
            'letra' => 'Vem Espirito Santo',
            'criado_por' => $adminMaster->id,
            'ativo' => true,
        ]);

        $dados = [
            'titulo' => 'Vem Espirito Santo',
            'artista' => 'ministerio teste',
            'letra' => 'Vem Espirito Santo aqui',
            'ativo' => '1',
        ];

        $this
            ->actingAs($adminMaster)
            ->post(route('admin.musicas.store'), $dados)
            ->assertRedirect()
            ->assertSessionHas('duplicidade_musica');

        $this->assertSame(1, Musica::query()->where('titulo', 'Vem Espirito Santo')->count());

        $this
            ->actingAs($adminMaster)
            ->post(route('admin.musicas.store'), $dados + ['confirmar_duplicidade' => '1'])
            ->assertSessionHasNoErrors();

        $this->assertSame(2, Musica::query()->where('titulo', 'Vem Espirito Santo')->count());
    }

    public function test_duplo_envio_da_mesma_musica_e_processado_uma_unica_vez(): void
    {
        $adminMaster = Usuario::factory()->adminMaster()->create([
            'primeiro_acesso' => false,
        ]);
        $token = 'submission-1234567890-abcdef';
        $dados = [
            '_submission_token' => $token,
            'titulo' => 'Canto enviado duas vezes',
            'artista' => 'Equipe de canto',
            'letra' => 'Uma única música deve ser criada',
            'ativo' => '1',
        ];

        $primeiraResposta = $this
            ->actingAs($adminMaster)
            ->post(route('admin.musicas.store'), $dados);

        $primeiraResposta->assertRedirect();

        $this
            ->actingAs($adminMaster)
            ->post(route('admin.musicas.store'), $dados)
            ->assertRedirect($primeiraResposta->headers->get('Location'))
            ->assertSessionHas('info', 'Este envio já havia sido processado. A segunda tentativa foi ignorada.');

        $this->assertSame(1, Musica::query()->where('titulo', 'Canto enviado duas vezes')->count());
    }

    public function test_destroy_de_musica_inativa_sem_excluir_do_banco(): void
    {
        $adminMaster = Usuario::factory()->adminMaster()->create([
            'primeiro_acesso' => false,
        ]);
        $musica = Musica::query()->create([
            'titulo' => 'Musica Para Inativar',
            'artista' => 'Artista',
            'letra' => 'Letra simples',
            'criado_por' => $adminMaster->id,
            'ativo' => true,
        ]);

        $this
            ->actingAs($adminMaster)
            ->delete(route('admin.musicas.destroy', $musica))
            ->assertRedirect(route('admin.musicas.index'))
            ->assertSessionHas('success', 'Musica inativada com sucesso.');

        $this->assertDatabaseHas('musicas', [
            'id' => $musica->id,
            'ativo' => false,
        ]);
    }

    public function test_admin_filtra_catalogo_por_cifras_momentos_e_status(): void
    {
        $adminMaster = Usuario::factory()->adminMaster()->create([
            'primeiro_acesso' => false,
        ]);

        $semCifra = Musica::query()->create([
            'titulo' => 'Canto Sem Cifra',
            'artista' => 'Equipe',
            'letra' => 'Letra simples',
            'criado_por' => $adminMaster->id,
            'ativo' => true,
        ]);
        $comCifra = Musica::query()->create([
            'titulo' => 'Canto Com Cifra',
            'artista' => 'Equipe',
            'letra' => 'Letra simples',
            'criado_por' => $adminMaster->id,
            'ativo' => true,
        ]);
        $inativa = Musica::query()->create([
            'titulo' => 'Canto Inativo',
            'artista' => 'Equipe',
            'letra' => 'Letra simples',
            'criado_por' => $adminMaster->id,
            'ativo' => false,
        ]);

        VersaoMusical::query()->create([
            'musica_id' => $comCifra->id,
            'titulo' => 'Cifra principal',
            'tom_musical' => 'C',
            'bpm' => 72,
            'letra_com_cifras' => '[C]Letra',
            'criado_por' => $adminMaster->id,
            'ativo' => true,
        ]);

        $this
            ->actingAs($adminMaster)
            ->get(route('admin.musicas.index', ['cifra' => 'sem']))
            ->assertOk()
            ->assertSee($semCifra->titulo)
            ->assertDontSee($comCifra->titulo)
            ->assertDontSee($inativa->titulo);

        $this
            ->actingAs($adminMaster)
            ->get(route('admin.musicas.index', ['cifra' => 'com']))
            ->assertOk()
            ->assertSee($comCifra->titulo)
            ->assertDontSee($semCifra->titulo);

        $this
            ->actingAs($adminMaster)
            ->get(route('admin.musicas.index', ['pendencia' => 'sem_momento']))
            ->assertOk()
            ->assertSee($semCifra->titulo)
            ->assertSee($comCifra->titulo);

        $this
            ->actingAs($adminMaster)
            ->get(route('admin.musicas.index', ['status' => 'inativas']))
            ->assertOk()
            ->assertSee($inativa->titulo)
            ->assertDontSee($semCifra->titulo);
    }
}
