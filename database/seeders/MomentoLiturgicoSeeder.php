<?php

namespace Database\Seeders;

use App\Models\MomentoLiturgico;
use Illuminate\Database\Seeder;

class MomentoLiturgicoSeeder extends Seeder
{
    public function run(): void
    {
        $momentosLiturgicos = [
            ['nome' => 'Entrada', 'ordem_exibicao' => 1, 'aliases' => ['Entrada']],
            ['nome' => 'Deus Trino', 'ordem_exibicao' => 2, 'aliases' => ['Deus Trino']],
            ['nome' => 'Ato', 'ordem_exibicao' => 3, 'aliases' => ['Ato']],
            ['nome' => 'Glória', 'ordem_exibicao' => 4, 'aliases' => ['Gloria', 'Glória']],
            ['nome' => 'Salmo', 'ordem_exibicao' => 5, 'aliases' => ['Salmo']],
            ['nome' => 'Evangelho', 'ordem_exibicao' => 6, 'aliases' => ['Evangelho']],
            ['nome' => 'Ofertório', 'ordem_exibicao' => 7, 'aliases' => ['Ofertorio', 'Ofertório']],
            ['nome' => 'Santo', 'ordem_exibicao' => 8, 'aliases' => ['Santo']],
            ['nome' => 'Pai Nosso', 'ordem_exibicao' => 9, 'aliases' => ['Pai Nosso']],
            ['nome' => 'Paz', 'ordem_exibicao' => 10, 'aliases' => ['Paz']],
            ['nome' => 'Cordeiro', 'ordem_exibicao' => 11, 'aliases' => ['Cordeiro']],
            ['nome' => 'Comunhão', 'ordem_exibicao' => 12, 'aliases' => ['Comunhao', 'Comunhão']],
            ['nome' => 'Pós-Comunhão', 'ordem_exibicao' => 13, 'aliases' => ['Pos-Comunhao', 'Pós-Comunhão']],
            ['nome' => 'Final', 'ordem_exibicao' => 14, 'aliases' => ['Final']],
        ];

        foreach ($momentosLiturgicos as $momentoLiturgico) {
            $registroExistente = MomentoLiturgico::query()
                ->whereIn('nome', $momentoLiturgico['aliases'])
                ->first();

            if ($registroExistente) {
                $registroExistente->nome = $momentoLiturgico['nome'];
                $registroExistente->descricao = $registroExistente->descricao ?? null;
                $registroExistente->ordem_exibicao = $momentoLiturgico['ordem_exibicao'];
                $registroExistente->ativo = true;
                $registroExistente->save();

                continue;
            }

            MomentoLiturgico::firstOrCreate(
                ['nome' => $momentoLiturgico['nome']],
                [
                    'descricao' => null,
                    'ordem_exibicao' => $momentoLiturgico['ordem_exibicao'],
                    'ativo' => true,
                ]
            );
        }
    }
}
