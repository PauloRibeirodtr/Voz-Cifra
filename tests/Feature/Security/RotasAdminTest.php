<?php

// cSpell:ignore acorde acordes numerico retorna musico igreja

namespace Tests\Feature\Security;

use App\Enums\PapelIgreja;
use App\Models\Acorde;
use App\Models\Igreja;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RotasAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_rota_de_acorde_com_id_nao_numerico_retorna_404(): void
    {
        /** @var Usuario $adminMaster */
        $adminMaster = Usuario::factory()->adminMaster()->create();

        $this
            ->actingAs($adminMaster)
            ->get('/admin/acordes/admin')
            ->assertNotFound();
    }

    public function test_rota_de_acorde_existente_sem_admin_master_retorna_403(): void
    {
        /** @var Igreja $igreja */
        $igreja = Igreja::factory()->create();
        /** @var Usuario $musico */
        $musico = Usuario::factory()->create();
        $musico->adicionarPapel(PapelIgreja::MUSICO, $igreja);

        /** @var Acorde $acorde */
        $acorde = Acorde::query()->create([
            'nome' => 'C',
            'descricao' => null,
            'dados_diagrama' => null,
            'ativo' => true,
        ]);

        $this
            ->actingAs($musico)
            ->withSession(['igreja_ativa_id' => $igreja->id])
            ->get(route('admin.acordes.show', $acorde))
            ->assertForbidden();
    }
}
