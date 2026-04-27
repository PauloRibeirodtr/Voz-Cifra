<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            AdminMasterSeeder::class,
            IgrejaCorumbaSeeder::class,
            TempoLiturgicoSeeder::class,
            MomentoLiturgicoSeeder::class,
            AcordeSeeder::class,
            MusicaExemploSeeder::class,
        ]);
    }
}
