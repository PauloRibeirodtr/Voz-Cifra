<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminMasterSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('usuarios')->updateOrInsert(
            ['email' => 'admin@ministeriomusical.com'],
            [
                'igreja_id' => null,
                'nome' => 'Administrador Master',
                'cpf' => env('ADMIN_MASTER_CPF', '00000000000'),
                'telefone' => env('ADMIN_MASTER_TELEFONE'),
                'password' => Hash::make(env('ADMIN_MASTER_PASSWORD', 'admin123456')),
                'perfil_global' => 'admin_master',
                'ativo' => true,
                'primeiro_acesso' => false,
                'remember_token' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }
}
