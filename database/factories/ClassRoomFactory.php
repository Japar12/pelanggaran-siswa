<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ClassRoom>
 */
class ClassRoomFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
             'name' => 'Kelas ' . $this->faker->randomElement(['X', 'XI', 'XII']) . ' ' . $this->faker->randomElement(['IPA', 'IPS', 'Bahasa']),
            'grade' => $this->faker->numberBetween(10, 12),
           'teacher_id' => User::role('guru')->inRandomOrder()->first()?->id,

        ];
    }
}
