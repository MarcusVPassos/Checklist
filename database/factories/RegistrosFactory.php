<?php

namespace Database\Factories;

use App\Models\Registros;
use Illuminate\Database\Eloquent\Factories\Factory;

class RegistrosFactory extends Factory
{
    protected $model = Registros::class;

    public function definition(): array
    {
        return [
            'tipo'        => $this->faker->randomElement(['carro', 'moto']),
            'placa'       => strtoupper($this->faker->unique()->bothify('???-####')),
            'marca_id'    => 1, // o seeder sobrescreve com uma marca válida
            'modelo'      => $this->faker->randomElement([
                'Onix LT', 'HB20 Comfort', 'Gol G6', 'Argo Drive',
                'Kwid Zen', 'Civic LX', 'Corolla GLi', 'Compass Sport',
            ]),
            'no_patio'    => $this->faker->boolean(30), // 30% no pátio
            'observacao'  => $this->faker->sentence(8), // se sua coluna é NOT NULL
            'reboque_condutor' => $this->faker->name(),
            'reboque_placa'    => strtoupper($this->faker->bothify('???-####')),
            // se 'assinatura_path' for NOT NULL na tabela, defina algo:
            'assinatura_path'  => 'assinaturas/fake-' . uniqid() . '.png',
        ];
    }
}
