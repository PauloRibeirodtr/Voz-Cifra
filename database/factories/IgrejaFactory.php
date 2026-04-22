<?php

namespace Database\Factories;

use App\Models\Igreja;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Igreja>
 */
class IgrejaFactory extends Factory
{
    protected $model = Igreja::class;

    public function definition(): array
    {
        $nome = 'Igreja ' . fake()->unique()->company();

        return [
            'nome' => $nome,
            'slug' => Str::slug($nome . '-' . fake()->unique()->numerify('###')),
            'cnpj' => fake()->unique()->numerify('##.###.###/####-##'),
            'cep' => fake()->numerify('#####-###'),
            'endereco' => fake()->streetAddress(),
            'cidade' => fake()->city(),
            'estado' => 'MS',
            'imagem_path' => null,
            'ativo' => true,
        ];
    }
}
