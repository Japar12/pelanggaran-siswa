<?php

namespace Database\Factories;

use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Violation>
 */
class ViolationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
             'student_id' => Student::inRandomOrder()->first()?->id ?? Student::factory(),
            'description' => $this->faker->randomElement([
                'Terlambat masuk sekolah',
                'Tidak memakai seragam lengkap',
                'Membuang sampah sembarangan',
                'Bolos tanpa izin',
                'Bertengkar dengan teman',
            ]),
            'points' => $this->faker->numberBetween(5, 50),
            'date' => $this->faker->dateTimeBetween('-2 months', 'now'),
        ];
    }
}
