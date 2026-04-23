<?php

namespace Database\Seeders;

use App\Models\Musica;
use App\Models\Usuario;
use App\Models\VersaoMusical;
use Illuminate\Database\Seeder;

class MusicaExemploSeeder extends Seeder
{
    public function run(): void
    {
        $adminMaster = Usuario::query()
            ->where('perfil_global', 'admin_master')
            ->orderBy('id')
            ->firstOrFail();

        $letraBase = <<<'TXT'
Quão grande é o meu Deus
Cantarei quão grande é o meu Deus
E todos hão de ver
Quão grande é o meu Deus

Com esplendor de um rei
Em majestade e luz
Faz a terra se alegrar, faz a terra se alegrar
Ele é a própria luz
E as trevas vão fugir
Tremer com Sua voz, tremer com Sua voz

Quão grande é o meu Deus
Cantarei quão grande é o meu Deus
E todos hão de ver
Quão grande é o meu Deus

Por gerações Ele é
O tempo está em Tuas mãos
O começo e o fim, o começo e o fim
Três se formam em um
Filho, Espírito e Pai
Cordeiro e Leão, Cordeiro e Leão

Quão grande é o meu Deus
Cantarei quão grande é o meu Deus
E todos hão de ver
Quão grande é o meu Deus

Sobre todo nome é o Seu
Tu és digno do louvor
Eu cantarei
Quão grande é o meu Deus

Quão grande é o meu Deus
Cantarei quão grande é o meu Deus
E todos hão de ver
Quão grande é o meu Deus
TXT;

        $letraComCifras = <<<'TXT'
[G]Quão grande é o meu [D/F#]Deus
[Em]Cantarei quão grande é o meu [C]Deus
[G]E todos hão de [D]ver
[C]Quão grande é o meu [G]Deus

[G]Com esplendor de um [D/F#]rei
[Em]Em majestade e [C]luz
[G]Faz a terra se a[D]legrar, faz a terra se a[C]legrar
[G]Ele é a própria [D/F#]luz
[Em]E as trevas vão fu[C]gir
[G]Tremer com Sua [D]voz, tremer com Sua [C]voz

[G]Quão grande é o meu [D/F#]Deus
[Em]Cantarei quão grande é o meu [C]Deus
[G]E todos hão de [D]ver
[C]Quão grande é o meu [G]Deus

[G]Por gerações Ele [D/F#]é
[Em]O tempo está em Tuas [C]mãos
[G]O começo e o [D]fim, o começo e o [C]fim
[G]Três se formam em [D/F#]um
[Em]Filho, Espírito e [C]Pai
[G]Cordeiro e Le[D]ão, Cordeiro e Le[C]ão

[G]Quão grande é o meu [D/F#]Deus
[Em]Cantarei quão grande é o meu [C]Deus
[G]E todos hão de [D]ver
[C]Quão grande é o meu [G]Deus

[Em]Sobre todo nome é o [C]Seu
[G]Tu és digno do lou[D]vor
[Em]Eu canta[C]rei
[C]Quão grande é o meu [G]Deus

[G]Quão grande é o meu [D/F#]Deus
[Em]Cantarei quão grande é o meu [C]Deus
[G]E todos hão de [D]ver
[C]Quão grande é o meu [G]Deus
TXT;

        $musica = Musica::query()->updateOrCreate(
            [
                'titulo' => 'Quão Grande É o Meu Deus',
            ],
            [
                'artista' => 'Soraya Moraes',
                'letra' => $letraBase,
                'criado_por' => $adminMaster->id,
                'ativo' => true,
            ]
        );

        VersaoMusical::query()->updateOrCreate(
            [
                'musica_id' => $musica->id,
                'titulo' => 'Versão principal em G',
            ],
            [
                'tom_musical' => 'G',
                'bpm' => 76,
                'youtube_video_id' => 'IT827htf_S8',
                'letra_com_cifras' => $letraComCifras,
                'criado_por' => $adminMaster->id,
                'ativo' => true,
            ]
        );
    }
}
