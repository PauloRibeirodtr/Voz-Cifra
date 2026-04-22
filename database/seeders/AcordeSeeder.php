<?php

namespace Database\Seeders;

use App\Models\Acorde;
use Illuminate\Database\Seeder;

class AcordeSeeder extends Seeder
{
    public function run(): void
    {
        
        //
        // Formato do `shape` (compatível com o editor visual):
        // - baseFret: inteiro, casa inicial do desenho (1 = primeira casa)
        // - variation_name: string opcional para identificar a variação/descrição visual
        // - root_note: nota raiz (ex: 'C', 'G#') — usado apenas para referência
        // - topMarkers: array com 6 posições representando as cordas de baixo->alto
        //     ordem: [string6, string5, string4, string3, string2, string1]
        //     valores: 'open' | 'muted' | null
        // - positions: array de objetos { string: 1..6, fret: inteiro, finger: 1..4 (opcional) }
        //     observe: `string` usa 1 = corda mais fina (mi agudo) até 6 = corda mais grossa (mi baixo)
        // - barres: array de objetos { fret: inteiro, fromString: 6..1, toString: 6..1 }
        //     fromString >= toString (ex: pestana completa: fromString=6, toString=1)
        //
        // O campo `dados_diagrama` é salvo como JSON (string) e o Controller decodifica automaticamente.
        $acordes = [
            [
                'nome' => 'C',
                'descricao' => 'C (aberto)',
                'shape' => [
                    'baseFret' => 1,
                    'variation_name' => 'open',
                    'root_note' => 'C',
                    'topMarkers' => ['muted', null, null, 'open', null, 'open'],
                    'positions' => [
                        ['string' => 5, 'fret' => 3, 'finger' => 3],
                        ['string' => 4, 'fret' => 2, 'finger' => 2],
                        ['string' => 2, 'fret' => 1, 'finger' => 1],
                    ],
                    'barres' => [],
                ],
            ],
            [
                'nome' => 'Am',
                'descricao' => 'A menor (aberto)',
                'shape' => [
                    'baseFret' => 1,
                    'variation_name' => 'open',
                    'root_note' => 'A',
                    'topMarkers' => ['open', 'open', 'open', null, null, 'open'],
                    'positions' => [
                        ['string' => 3, 'fret' => 2, 'finger' => 2],
                        ['string' => 2, 'fret' => 1, 'finger' => 1],
                        ['string' => 4, 'fret' => 2, 'finger' => 3],
                    ],
                    'barres' => [],
                ],
            ],
            [
                'nome' => 'G',
                'descricao' => 'G (aberto)',
                'shape' => [
                    'baseFret' => 1,
                    'variation_name' => 'open',
                    'root_note' => 'G',
                    'topMarkers' => [null, null, null, 'open', null, 'open'],
                    'positions' => [
                        ['string' => 6, 'fret' => 3, 'finger' => 2],
                        ['string' => 1, 'fret' => 3, 'finger' => 3],
                        ['string' => 2, 'fret' => 3, 'finger' => 4],
                    ],
                    'barres' => [],
                ],
            ],
            [
                'nome' => 'D',
                'descricao' => 'D (aberto)',
                'shape' => [
                    'baseFret' => 1,
                    'variation_name' => 'open',
                    'root_note' => 'D',
                    'topMarkers' => ['open', null, null, null, 'open', 'open'],
                    'positions' => [
                        ['string' => 3, 'fret' => 2, 'finger' => 1],
                        ['string' => 1, 'fret' => 2, 'finger' => 2],
                        ['string' => 2, 'fret' => 3, 'finger' => 3],
                    ],
                    'barres' => [],
                ],
            ],
            [
                'nome' => 'E',
                'descricao' => 'E (aberto)',
                'shape' => [
                    'baseFret' => 1,
                    'variation_name' => 'open',
                    'root_note' => 'E',
                    'topMarkers' => ['open', 'open', 'open', 'open', 'open', 'open'],
                    'positions' => [
                        ['string' => 5, 'fret' => 2, 'finger' => 2],
                        ['string' => 4, 'fret' => 2, 'finger' => 3],
                        ['string' => 3, 'fret' => 1, 'finger' => 1],
                    ],
                    'barres' => [],
                ],
            ],
            [
                'nome' => 'Em',
                'descricao' => 'E menor (aberto)',
                'shape' => [
                    'baseFret' => 1,
                    'variation_name' => 'open',
                    'root_note' => 'E',
                    'topMarkers' => ['open', 'open', 'open', 'open', 'open', 'open'],
                    'positions' => [
                        ['string' => 5, 'fret' => 2, 'finger' => 2],
                        ['string' => 4, 'fret' => 2, 'finger' => 3],
                    ],
                    'barres' => [],
                ],
            ],
            [
                'nome' => 'A',
                'descricao' => 'A (aberto)',
                'shape' => [
                    'baseFret' => 1,
                    'variation_name' => 'open',
                    'root_note' => 'A',
                    'topMarkers' => ['open', 'open', 'open', 'open', 'open', 'open'],
                    'positions' => [
                        ['string' => 4, 'fret' => 2, 'finger' => 1],
                        ['string' => 3, 'fret' => 2, 'finger' => 2],
                        ['string' => 2, 'fret' => 2, 'finger' => 3],
                    ],
                    'barres' => [],
                ],
            ],
            [
                'nome' => 'Dm',
                'descricao' => 'D menor (aberto)',
                'shape' => [
                    'baseFret' => 1,
                    'variation_name' => 'open',
                    'root_note' => 'D',
                    'topMarkers' => ['open', null, null, null, 'open', 'open'],
                    'positions' => [
                        ['string' => 1, 'fret' => 1, 'finger' => 1],
                        ['string' => 3, 'fret' => 2, 'finger' => 2],
                        ['string' => 2, 'fret' => 3, 'finger' => 3],
                    ],
                    'barres' => [],
                ],
            ],
            [
                'nome' => 'G7',
                'descricao' => 'G7 (variação)',
                'shape' => [
                    'baseFret' => 1,
                    'variation_name' => 'open',
                    'root_note' => 'G',
                    'topMarkers' => [null, null, null, 'open', null, 'open'],
                    'positions' => [
                        ['string' => 1, 'fret' => 1, 'finger' => 1],
                        ['string' => 6, 'fret' => 3, 'finger' => 2],
                    ],
                    'barres' => [],
                ],
            ],
            [
                'nome' => 'C7',
                'descricao' => 'C7 (aberto)',
                'shape' => [
                    'baseFret' => 1,
                    'variation_name' => 'open',
                    'root_note' => 'C',
                    'topMarkers' => ['muted', null, null, 'open', null, 'open'],
                    'positions' => [
                        ['string' => 2, 'fret' => 1, 'finger' => 1],
                        ['string' => 5, 'fret' => 3, 'finger' => 3],
                    ],
                    'barres' => [],
                ],
            ],
            [
                'nome' => 'A7',
                'descricao' => 'A7 (aberto)',
                'shape' => [
                    'baseFret' => 1,
                    'variation_name' => 'open',
                    'root_note' => 'A',
                    'topMarkers' => ['open', 'open', 'open', 'open', 'open', 'open'],
                    'positions' => [
                        ['string' => 2, 'fret' => 2, 'finger' => 2],
                        ['string' => 4, 'fret' => 2, 'finger' => 1],
                    ],
                    'barres' => [],
                ],
            ],
            [
                'nome' => 'E7',
                'descricao' => 'E7 (aberto)',
                'shape' => [
                    'baseFret' => 1,
                    'variation_name' => 'open',
                    'root_note' => 'E',
                    'topMarkers' => ['open', 'open', 'open', 'open', 'open', 'open'],
                    'positions' => [
                        ['string' => 3, 'fret' => 1, 'finger' => 1],
                        ['string' => 5, 'fret' => 2, 'finger' => 2],
                    ],
                    'barres' => [],
                ],
            ],
            [
                'nome' => 'Bm',
                'descricao' => 'B menor (pestana em 2)',
                'shape' => [
                    'baseFret' => 1,
                    'variation_name' => 'barre',
                    'root_note' => 'B',
                    'topMarkers' => [null, null, null, null, null, null],
                    'positions' => [
                        ['string' => 2, 'fret' => 3, 'finger' => 3],
                        ['string' => 3, 'fret' => 4, 'finger' => 4],
                    ],
                    'barres' => [
                        ['fret' => 2, 'fromString' => 5, 'toString' => 1],
                    ],
                ],
            ],
            [
                'nome' => 'F',
                'descricao' => 'F (pestana completa 1)',
                'shape' => [
                    'baseFret' => 1,
                    'variation_name' => 'barre',
                    'root_note' => 'F',
                    'topMarkers' => [null, null, null, null, null, null],
                    'positions' => [
                        ['string' => 2, 'fret' => 1, 'finger' => 1],
                        ['string' => 3, 'fret' => 2, 'finger' => 2],
                        ['string' => 4, 'fret' => 3, 'finger' => 4],
                    ],
                    'barres' => [
                        ['fret' => 1, 'fromString' => 6, 'toString' => 1],
                    ],
                ],
            ],
            [
                'nome' => 'Fm',
                'descricao' => 'F menor (pestana)',
                'shape' => [
                    'baseFret' => 1,
                    'variation_name' => 'barre',
                    'root_note' => 'F',
                    'topMarkers' => [null, null, null, null, null, null],
                    'positions' => [
                        ['string' => 3, 'fret' => 1, 'finger' => 1],
                        ['string' => 4, 'fret' => 3, 'finger' => 3],
                    ],
                    'barres' => [
                        ['fret' => 1, 'fromString' => 6, 'toString' => 1],
                    ],
                ],
            ],
            [
                'nome' => 'Cmaj7',
                'descricao' => 'Cmaj7 (aberto)',
                'shape' => [
                    'baseFret' => 1,
                    'variation_name' => 'open',
                    'root_note' => 'C',
                    'topMarkers' => ['muted', null, null, 'open', null, 'open'],
                    'positions' => [
                        ['string' => 2, 'fret' => 1, 'finger' => 1],
                        ['string' => 4, 'fret' => 2, 'finger' => 2],
                    ],
                    'barres' => [],
                ],
            ],
            [
                'nome' => 'Bb',
                'descricao' => 'Bb (pestana 1, variação)',
                'shape' => [
                    'baseFret' => 1,
                    'variation_name' => 'barre',
                    'root_note' => 'Bb',
                    'topMarkers' => [null, null, null, null, null, null],
                    'positions' => [
                        ['string' => 3, 'fret' => 3, 'finger' => 3],
                    ],
                    'barres' => [
                        ['fret' => 1, 'fromString' => 5, 'toString' => 1],
                    ],
                ],
            ],
            // 20 acordes adicionais
            [ 'nome' => 'C#', 'descricao' => 'C# (variação)', 'shape' => ['baseFret'=>1,'variation_name'=>'open','root_note'=>'C#','topMarkers'=>['muted',null,null,'open',null,'open'],'positions'=>[['string'=>5,'fret'=>4,'finger'=>3],['string'=>4,'fret'=>6,'finger'=>4]],'barres'=>[]] ],
            [ 'nome' => 'C#m', 'descricao' => 'C# menor', 'shape' => ['baseFret'=>1,'variation_name'=>'barre','root_note'=>'C#','topMarkers'=>[null,null,null,null,null,null],'positions'=>[['string'=>4,'fret'=>6,'finger'=>2],['string'=>3,'fret'=>6,'finger'=>3]],'barres'=>[['fret'=>4,'fromString'=>6,'toString'=>1]]] ],
            [ 'nome' => 'F#', 'descricao' => 'F# (variação)', 'shape' => ['baseFret'=>1,'variation_name'=>'open','root_note'=>'F#','topMarkers'=>[null,'open',null,null,'open',null],'positions'=>[['string'=>6,'fret'=>2,'finger'=>1]],'barres'=>[]] ],
            [ 'nome' => 'F#m', 'descricao' => 'F# menor', 'shape' => ['baseFret'=>1,'variation_name'=>'barre','root_note'=>'F#','topMarkers'=>[null,null,null,null,null,null],'positions'=>[['string'=>5,'fret'=>4,'finger'=>2]],'barres'=>[['fret'=>2,'fromString'=>6,'toString'=>1]]] ],
            [ 'nome' => 'G#', 'descricao' => 'G# (variação)', 'shape' => ['baseFret'=>1,'variation_name'=>'open','root_note'=>'G#','topMarkers'=>[null,null,null,'open',null,'open'],'positions'=>[['string'=>5,'fret'=>6,'finger'=>3]],'barres'=>[]] ],
            [ 'nome' => 'G#m', 'descricao' => 'G# menor', 'shape' => ['baseFret'=>1,'variation_name'=>'barre','root_note'=>'G#','topMarkers'=>[null,null,null,null,null,null],'positions'=>[['string'=>4,'fret'=>6,'finger'=>2]],'barres'=>[['fret'=>4,'fromString'=>6,'toString'=>1]]] ],
            [ 'nome' => 'D#', 'descricao' => 'D# (variação)', 'shape' => ['baseFret'=>1,'variation_name'=>'open','root_note'=>'D#','topMarkers'=>['open',null,null,'open',null,'open'],'positions'=>[['string'=>4,'fret'=>1,'finger'=>1]],'barres'=>[]] ],
            [ 'nome' => 'D#m', 'descricao' => 'D# menor', 'shape' => ['baseFret'=>1,'variation_name'=>'barre','root_note'=>'D#','topMarkers'=>[null,null,null,null,null,null],'positions'=>[['string'=>3,'fret'=>6,'finger'=>3]],'barres'=>[['fret'=>6,'fromString'=>5,'toString'=>1]]] ],
            [ 'nome' => 'Bbmaj7', 'descricao' => 'Bb maj7', 'shape' => ['baseFret'=>1,'variation_name'=>'open','root_note'=>'Bb','topMarkers'=>[null,null,null,null,'open','open'],'positions'=>[['string'=>2,'fret'=>3,'finger'=>1]],'barres'=>[]] ],
            [ 'nome' => 'Amaj7', 'descricao' => 'A maj7', 'shape' => ['baseFret'=>1,'variation_name'=>'open','root_note'=>'A','topMarkers'=>['open','open','open','open','open','open'],'positions'=>[['string'=>2,'fret'=>1,'finger'=>1]],'barres'=>[]] ],
            [ 'nome' => 'Asus2', 'descricao' => 'A sus2', 'shape' => ['baseFret'=>1,'variation_name'=>'open','root_note'=>'A','topMarkers'=>['open','open','open','open','open','open'],'positions'=>[['string'=>2,'fret'=>2,'finger'=>2]],'barres'=>[]] ],
            [ 'nome' => 'Csus2', 'descricao' => 'C sus2', 'shape' => ['baseFret'=>1,'variation_name'=>'open','root_note'=>'C','topMarkers'=>['muted',null,null,'open',null,'open'],'positions'=>[['string'=>2,'fret'=>1,'finger'=>1]],'barres'=>[]] ],
            [ 'nome' => 'Csus4', 'descricao' => 'C sus4', 'shape' => ['baseFret'=>1,'variation_name'=>'open','root_note'=>'C','topMarkers'=>['muted',null,null,'open',null,'open'],'positions'=>[['string'=>3,'fret'=>5,'finger'=>3]],'barres'=>[]] ],
            [ 'nome' => 'Dsus2', 'descricao' => 'D sus2', 'shape' => ['baseFret'=>1,'variation_name'=>'open','root_note'=>'D','topMarkers'=>['open',null,null,null,'open','open'],'positions'=>[['string'=>3,'fret'=>2,'finger'=>1]],'barres'=>[]] ],
            [ 'nome' => 'Esus4', 'descricao' => 'E sus4', 'shape' => ['baseFret'=>1,'variation_name'=>'open','root_note'=>'E','topMarkers'=>['open','open','open','open','open','open'],'positions'=>[['string'=>3,'fret'=>2,'finger'=>2]],'barres'=>[]] ],
            [ 'nome' => 'Am7', 'descricao' => 'A menor 7', 'shape' => ['baseFret'=>1,'variation_name'=>'open','root_note'=>'A','topMarkers'=>['open','open','open',null,'open','open'],'positions'=>[['string'=>2,'fret'=>1,'finger'=>1],['string'=>4,'fret'=>2,'finger'=>2]],'barres'=>[]] ],
            [ 'nome' => 'Em7', 'descricao' => 'E menor 7', 'shape' => ['baseFret'=>1,'variation_name'=>'open','root_note'=>'E','topMarkers'=>['open','open','open','open','open','open'],'positions'=>[['string'=>5,'fret'=>2,'finger'=>2],['string'=>4,'fret'=>2,'finger'=>3]],'barres'=>[]] ],
            [ 'nome' => 'B7', 'descricao' => 'B7 (variação)', 'shape' => ['baseFret'=>1,'variation_name'=>'open','root_note'=>'B','topMarkers'=>[null,null,null,null,'open','open'],'positions'=>[['string'=>3,'fret'=>2,'finger'=>1],['string'=>5,'fret'=>2,'finger'=>2]],'barres'=>[]] ],
            [ 'nome' => 'D7sus4', 'descricao' => 'D7 sus4', 'shape' => ['baseFret'=>1,'variation_name'=>'open','root_note'=>'D','topMarkers'=>['open',null,null,null,'open','open'],'positions'=>[['string'=>3,'fret'=>2,'finger'=>1],['string'=>2,'fret'=>3,'finger'=>2]],'barres'=>[]] ],
            [ 'nome' => 'Gsus4', 'descricao' => 'G sus4', 'shape' => ['baseFret'=>1,'variation_name'=>'open','root_note'=>'G','topMarkers'=>[null,null,null,'open',null,'open'],'positions'=>[['string'=>3,'fret'=>5,'finger'=>1]],'barres'=>[]] ],
        ];

        foreach ($acordes as $item) {
            $nome = trim((string) $item['nome']);
            $descricao = trim((string) ($item['descricao'] ?? 'Acorde gerado pelo seeder'));
            $shape = $item['shape'] ?? null;

            Acorde::updateOrCreate(
                ['nome' => $nome, 'descricao' => $descricao],
                [
                    'descricao' => $descricao,
                    'dados_diagrama' => $shape,
                    'ativo' => true,
                ]
            );
        }
    }
}
