<?php

namespace Database\Seeders;

use App\Models\TempoLiturgico;
use Illuminate\Database\Seeder;

class TempoLiturgicoSeeder extends Seeder
{
    public function run(): void
    {
        $temposLiturgicos = [
            ['nome' => 'Advento', 'aliases' => ['Advento']],
            ['nome' => 'Natal', 'aliases' => ['Natal']],
            ['nome' => 'Quaresma', 'aliases' => ['Quaresma']],
            ['nome' => 'Páscoa', 'aliases' => ['Pascoa', 'Páscoa']],
            ['nome' => 'Tempo Comum', 'aliases' => ['Tempo Comum']],
        ];

        foreach ($temposLiturgicos as $tempoLiturgico) {
            $registroExistente = TempoLiturgico::query()
                ->whereIn('nome', $tempoLiturgico['aliases'])
                ->first();

            if ($registroExistente) {
                $registroExistente->nome = $tempoLiturgico['nome'];
                $registroExistente->descricao = $registroExistente->descricao ?? null;
                $registroExistente->ativo = true;
                $registroExistente->save();

                continue;
            }

            TempoLiturgico::firstOrCreate(
                ['nome' => $tempoLiturgico['nome']],
                [
                    'descricao' => null,
                    'ativo' => true,
                ]
            );
        }
    }
}
