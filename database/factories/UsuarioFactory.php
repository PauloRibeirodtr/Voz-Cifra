<?php

namespace Database\Factories;

use App\Models\Usuario;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Usuario>
 */
class UsuarioFactory extends Factory
{
    protected $model = Usuario::class;

    public function definition(): array
    {
        return [
            'igreja_id' => null,
            'nome' => fake()->name(),
            'cpf' => fake()->unique()->numerify('###########'),
            'email' => fake()->unique()->safeEmail(),
            'telefone' => fake()->numerify('(##) #####-####'),
            'password' => 'password123',
            'perfil_global' => 'usuario',
            'nivel_global' => 1,
            'eh_padre' => false,
            'ativo' => true,
            'primeiro_acesso' => false,
            'theme_preference' => 'system',
            'remember_token' => Str::random(10),
        ];
    }

    public function adminMaster(): static
    {
        return $this->state(fn () => [
            'perfil_global' => 'admin_master',
            'nivel_global' => 6,
        ]);
    }

    public function padre(): static
    {
        return $this->state(fn () => [
            'eh_padre' => true,
        ]);
    }

    public function primeiroAcesso(): static
    {
        return $this->state(fn () => [
            'primeiro_acesso' => true,
        ]);
    }
}
