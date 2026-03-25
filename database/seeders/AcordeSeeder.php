<?php

namespace Database\Seeders;

use App\Models\Acorde;
use Illuminate\Database\Seeder;

class AcordeSeeder extends Seeder
{
    public function run(): void
    {
        $acordes = [
            'C',
            'D',
            'E',
            'F',
            'G',
            'A',
            'B',
            'Am',
            'Em',
            'Dm',
            'G7',
            'C7',
        ];

        foreach ($acordes as $nome) {
            Acorde::updateOrCreate(
                ['nome' => $nome],
                [
                    'descricao' => 'Acorde basico para apoio na digitacao de cifras.',
                    'dados_diagrama' => null,
                    'ativo' => true,
                ]
            );
        }
    }
}
