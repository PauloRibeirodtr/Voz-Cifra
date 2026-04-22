<?php

namespace Database\Seeders;

use App\Models\Usuario;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminMasterSeeder extends Seeder
{
    public function run(): void
    {
        $cpf = preg_replace('/\D+/', '', (string) env('ADMIN_MASTER_CPF', '06070933150')) ?: '06070933150';
        $email = trim((string) env('ADMIN_MASTER_EMAIL', 'pythonocr7@gmail.com'));
        $nivelGlobal = (int) env('ADMIN_MASTER_NIVEL_GLOBAL', 6);
        $nivelGlobal = in_array($nivelGlobal, [6, 7], true) ? $nivelGlobal : 6;
        $primeiroAcesso = filter_var(env('ADMIN_MASTER_PRIMEIRO_ACESSO', false), FILTER_VALIDATE_BOOL);

        $dados = [
            'igreja_id' => null,
            'nome' => env('ADMIN_MASTER_NOME', 'Administrador Master'),
            'cpf' => $cpf,
            'email' => $email,
            'telefone' => env('ADMIN_MASTER_TELEFONE'),
            'password' => Hash::make(env('ADMIN_MASTER_PASSWORD', 'admin123456')),
            'perfil_global' => 'admin_master',
            'nivel_global' => $nivelGlobal,
            'eh_padre' => false,
            'ativo' => true,
            'primeiro_acesso' => $primeiroAcesso,
            'theme_preference' => 'system',
            'remember_token' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        $usuario = Usuario::query()
            ->where('email', $email)
            ->orWhere('cpf', $cpf)
            ->first();

        if ($usuario) {
            $usuario->forceFill($dados)->save();

            return;
        }

        Usuario::query()->create($dados);
    }
}
