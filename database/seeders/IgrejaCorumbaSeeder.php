<?php

namespace Database\Seeders;

use App\Models\Igreja;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class IgrejaCorumbaSeeder extends Seeder
{
    public function run(): void
    {
        Igreja::query()->updateOrCreate(
            [
                'slug' => 'catedral-nossa-senhora-da-candelaria-corumba',
            ],
            [
                'nome' => 'Catedral de Nossa Senhora da Candelária',
                'slug_publico_musicos' => 'catedral-nossa-senhora-da-candelaria-corumba',
                'cnpj' => '00.000.000/0001-01',
                'cep' => '79301-140',
                'endereco' => 'Pça. da República',
                'numero' => '1',
                'bairro' => 'Centro',
                'cidade' => 'Corumbá',
                'estado' => 'MS',
                'imagem_path' => $this->resolverImagemPath(),
                'status_operacional' => 'aguardando_admin_local',
                'ativo' => true,
            ]
        );
    }

    private function resolverImagemPath(): ?string
    {
        $origem = public_path('igrejas/igreja.jpg');

        if (!File::exists($origem)) {
            return null;
        }

        $destinoRelativo = 'igrejas/imagens/catedral-nossa-senhora-da-candelaria-corumba.jpg';
        Storage::disk('public')->put($destinoRelativo, File::get($origem));

        return $destinoRelativo;
    }
}
