<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Inscripcion>
 */
class InscripcionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'aceptado' => fake()->boolean(),
            'fechaInscripcion' => fake()->date(),
            'estudiante_id' => fake()->numberBetween(1,30),
            'curso_id' => fake()->numberBetween(1,36),
        ];
    }
}
