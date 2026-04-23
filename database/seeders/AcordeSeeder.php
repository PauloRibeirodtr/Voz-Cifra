<?php

namespace Database\Seeders;

use App\Models\Acorde;
use Illuminate\Database\Seeder;

class AcordeSeeder extends Seeder
{
    public function run(): void
    {
        $acordes = [
            $this->acorde('A', 'A maior aberto', 1, 'A', ['muted', 'open', null, null, null, 'open'], [
                ['string' => 4, 'fret' => 2, 'finger' => 1],
                ['string' => 3, 'fret' => 2, 'finger' => 2],
                ['string' => 2, 'fret' => 2, 'finger' => 3],
            ]),
            $this->acorde('Am', 'A menor aberto', 1, 'A', ['muted', 'open', null, null, null, 'open'], [
                ['string' => 4, 'fret' => 2, 'finger' => 2],
                ['string' => 3, 'fret' => 2, 'finger' => 3],
                ['string' => 2, 'fret' => 1, 'finger' => 1],
            ]),
            $this->acorde('A7', 'A7 aberto', 1, 'A', ['muted', 'open', null, 'open', null, 'open'], [
                ['string' => 4, 'fret' => 2, 'finger' => 2],
                ['string' => 2, 'fret' => 2, 'finger' => 3],
            ]),
            $this->acorde('Asus2', 'A sus2 aberto', 1, 'A', ['muted', 'open', null, null, 'open', 'open'], [
                ['string' => 4, 'fret' => 2, 'finger' => 2],
                ['string' => 3, 'fret' => 2, 'finger' => 3],
            ]),
            $this->acorde('B7', 'B7 aberto', 1, 'B', ['muted', null, null, null, 'open', 'open'], [
                ['string' => 5, 'fret' => 2, 'finger' => 2],
                ['string' => 4, 'fret' => 1, 'finger' => 1],
                ['string' => 3, 'fret' => 2, 'finger' => 3],
                ['string' => 1, 'fret' => 2, 'finger' => 4],
            ]),
            $this->acorde('Bm', 'B menor com pestana na segunda casa', 2, 'B', ['muted', null, null, null, null, null], [
                ['string' => 4, 'fret' => 4, 'finger' => 3],
                ['string' => 3, 'fret' => 4, 'finger' => 4],
                ['string' => 2, 'fret' => 3, 'finger' => 2],
            ], [
                ['fret' => 2, 'fromString' => 5, 'toString' => 1],
            ]),
            $this->acorde('C', 'C maior aberto', 1, 'C', ['muted', null, null, 'open', null, 'open'], [
                ['string' => 5, 'fret' => 3, 'finger' => 3],
                ['string' => 4, 'fret' => 2, 'finger' => 2],
                ['string' => 2, 'fret' => 1, 'finger' => 1],
            ]),
            $this->acorde('Cmaj7', 'C maior com setima maior', 1, 'C', ['muted', null, null, 'open', 'open', 'open'], [
                ['string' => 5, 'fret' => 3, 'finger' => 3],
                ['string' => 4, 'fret' => 2, 'finger' => 2],
            ]),
            $this->acorde('D', 'D maior aberto', 1, 'D', ['muted', 'muted', 'open', null, null, null], [
                ['string' => 3, 'fret' => 2, 'finger' => 1],
                ['string' => 1, 'fret' => 2, 'finger' => 2],
                ['string' => 2, 'fret' => 3, 'finger' => 3],
            ]),
            $this->acorde('D7', 'D7 aberto', 1, 'D', ['muted', 'muted', 'open', null, null, null], [
                ['string' => 2, 'fret' => 1, 'finger' => 1],
                ['string' => 3, 'fret' => 2, 'finger' => 2],
                ['string' => 1, 'fret' => 2, 'finger' => 3],
            ]),
            $this->acorde('Dm', 'D menor aberto', 1, 'D', ['muted', 'muted', 'open', null, null, null], [
                ['string' => 1, 'fret' => 1, 'finger' => 1],
                ['string' => 3, 'fret' => 2, 'finger' => 2],
                ['string' => 2, 'fret' => 3, 'finger' => 3],
            ]),
            $this->acorde('D/F#', 'D com baixo em F#', 1, 'D', [null, 'muted', 'open', null, null, null], [
                ['string' => 6, 'fret' => 2, 'finger' => 1],
                ['string' => 3, 'fret' => 2, 'finger' => 2],
                ['string' => 2, 'fret' => 3, 'finger' => 3],
                ['string' => 1, 'fret' => 2, 'finger' => 4],
            ]),
            $this->acorde('Dsus2', 'D sus2 aberto', 1, 'D', ['muted', 'muted', 'open', null, 'open', null], [
                ['string' => 1, 'fret' => 2, 'finger' => 1],
                ['string' => 2, 'fret' => 3, 'finger' => 3],
            ]),
            $this->acorde('D7sus4', 'D7 sus4 aberto', 1, 'D', ['muted', 'muted', 'open', null, null, null], [
                ['string' => 3, 'fret' => 2, 'finger' => 1],
                ['string' => 2, 'fret' => 3, 'finger' => 2],
                ['string' => 1, 'fret' => 3, 'finger' => 3],
            ]),
            $this->acorde('E', 'E maior aberto', 1, 'E', ['open', 'open', null, null, null, 'open'], [
                ['string' => 5, 'fret' => 2, 'finger' => 2],
                ['string' => 4, 'fret' => 2, 'finger' => 3],
                ['string' => 3, 'fret' => 1, 'finger' => 1],
            ]),
            $this->acorde('Em', 'E menor aberto', 1, 'E', ['open', 'open', null, null, 'open', 'open'], [
                ['string' => 5, 'fret' => 2, 'finger' => 2],
                ['string' => 4, 'fret' => 2, 'finger' => 3],
            ]),
            $this->acorde('E7', 'E7 aberto', 1, 'E', ['open', 'open', null, 'open', 'open', 'open'], [
                ['string' => 5, 'fret' => 2, 'finger' => 2],
                ['string' => 3, 'fret' => 1, 'finger' => 1],
            ]),
            $this->acorde('Esus4', 'E sus4 aberto', 1, 'E', ['open', 'open', null, null, 'open', 'open'], [
                ['string' => 5, 'fret' => 2, 'finger' => 2],
                ['string' => 4, 'fret' => 2, 'finger' => 3],
                ['string' => 3, 'fret' => 2, 'finger' => 4],
            ]),
            $this->acorde('F', 'F maior com pestana na primeira casa', 1, 'F', [null, null, null, null, null, null], [
                ['string' => 5, 'fret' => 3, 'finger' => 4],
                ['string' => 4, 'fret' => 3, 'finger' => 3],
                ['string' => 3, 'fret' => 2, 'finger' => 2],
            ], [
                ['fret' => 1, 'fromString' => 6, 'toString' => 1],
            ]),
            $this->acorde('F#m', 'F# menor com pestana na segunda casa', 2, 'F#', [null, null, null, null, null, null], [
                ['string' => 5, 'fret' => 4, 'finger' => 3],
                ['string' => 4, 'fret' => 4, 'finger' => 4],
            ], [
                ['fret' => 2, 'fromString' => 6, 'toString' => 1],
            ]),
            $this->acorde('G', 'G maior aberto', 1, 'G', [null, null, 'open', 'open', null, null], [
                ['string' => 6, 'fret' => 3, 'finger' => 2],
                ['string' => 2, 'fret' => 3, 'finger' => 3],
                ['string' => 1, 'fret' => 3, 'finger' => 4],
            ]),
            $this->acorde('G/B', 'G com baixo em B', 1, 'G', ['muted', null, 'open', 'open', null, null], [
                ['string' => 5, 'fret' => 2, 'finger' => 1],
                ['string' => 2, 'fret' => 3, 'finger' => 3],
                ['string' => 1, 'fret' => 3, 'finger' => 4],
            ]),
            $this->acorde('G7', 'G7 aberto', 1, 'G', [null, null, 'open', 'open', 'open', null], [
                ['string' => 6, 'fret' => 3, 'finger' => 2],
                ['string' => 1, 'fret' => 1, 'finger' => 1],
            ]),
            $this->acorde('Gsus4', 'G sus4 aberto', 1, 'G', [null, null, 'open', 'open', null, null], [
                ['string' => 6, 'fret' => 3, 'finger' => 2],
                ['string' => 2, 'fret' => 1, 'finger' => 1],
                ['string' => 1, 'fret' => 3, 'finger' => 4],
            ]),
        ];

        foreach ($acordes as $item) {
            Acorde::updateOrCreate(
                ['nome' => $item['nome']],
                [
                    'descricao' => $item['descricao'],
                    'dados_diagrama' => $item['dados_diagrama'],
                    'ativo' => true,
                ]
            );
        }
    }

    private function acorde(
        string $nome,
        string $descricao,
        int $baseFret,
        string $rootNote,
        array $topMarkers,
        array $positions,
        array $barres = []
    ): array {
        return [
            'nome' => $nome,
            'descricao' => $descricao,
            'dados_diagrama' => [
                'baseFret' => $baseFret,
                'variation_name' => $baseFret > 1 || $barres !== [] ? 'barre' : 'open',
                'root_note' => $rootNote,
                'topMarkers' => $topMarkers,
                'positions' => $positions,
                'barres' => $barres,
            ],
        ];
    }
}
