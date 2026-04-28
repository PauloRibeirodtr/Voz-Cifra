<?php

namespace Database\Seeders;

use App\Models\Usuario;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminMasterSeeder extends Seeder
{
    private const DEFAULT_ADMIN_MASTER_PASSWORD = 'admin123456';

    public function run(): void
    {
        $cpf = preg_replace('/\D+/', '', (string) env('ADMIN_MASTER_CPF', '06070933150')) ?: '06070933150';
        $email = trim((string) env('ADMIN_MASTER_EMAIL', 'pythonocr7@gmail.com'));
        $senhaInformada = trim((string) env('ADMIN_MASTER_PASSWORD', self::DEFAULT_ADMIN_MASTER_PASSWORD));
        $primeiroAcesso = filter_var(env('ADMIN_MASTER_PRIMEIRO_ACESSO', true), FILTER_VALIDATE_BOOL);

        $usuarioExistente = Usuario::query()
            ->where('email', $email)
            ->orWhere('cpf', $cpf)
            ->first();

        if ($usuarioExistente instanceof Usuario) {
            $usuarioExistente->forceFill([
                'email' => $usuarioExistente->email ?: $email,
                'cpf' => $usuarioExistente->cpf ?: $cpf,
                'perfil_global' => 'admin_master',
                'nivel_global' => 6,
                'ativo' => true,
                'eh_padre' => false,
            ])->save();

            return;
        }

        $dados = [
            'igreja_id' => null,
            'nome' => env('ADMIN_MASTER_NOME', 'Administrador Master'),
            'cpf' => $cpf,
            'email' => $email,
            'telefone' => env('ADMIN_MASTER_TELEFONE'),
            'password' => Hash::make($senhaInformada !== '' ? $senhaInformada : self::DEFAULT_ADMIN_MASTER_PASSWORD),
            'perfil_global' => 'admin_master',
            'nivel_global' => 6,
            'eh_padre' => false,
            'ativo' => true,
            'primeiro_acesso' => $primeiroAcesso,
            'theme_preference' => 'system',
            'remember_token' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        Usuario::query()->create($dados);
    }
}
