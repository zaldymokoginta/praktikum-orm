<?php

namespace Database\Factories;

use App\Models\Mahasiswa;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Mahasiswa>
 */
class MahasiswaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $angkatan = fake()->numberBetween(2021, 2025);

        return [
            'nim'      => substr((string) $angkatan, 2) . fake()->unique()->numerify('#####'),
            'nama'     => fake()->name(),
            'prodi'    => fake()->randomElement(['Teknik Informatika', 'Sistem Informasi', 'Bisnis Digital']),
            'angkatan' => $angkatan,
        ];
    }
}
