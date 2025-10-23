<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\ClassRoom;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Student>
 */
class StudentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
      // ambil user dengan role 'siswa'
        $user = User::role('siswa')->inRandomOrder()->first();

        return [
            'user_id' => $user?->id, // pastikan terhubung ke user
            'name' => $user?->name ?? $this->faker->name(),
            'nisn' => $this->faker->unique()->numerify('##########'),
            'class_room_id' => ClassRoom::inRandomOrder()->first()?->id ?? ClassRoom::factory(),
        ];
    }
}
