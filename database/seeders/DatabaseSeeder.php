<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Violation;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
       // Jalankan seeder secara berurutan
        $this->call([
            UserSeeder::class,
            ClassRoomSeeder::class,
            StudentSeeder::class,
        ]);
        Violation::factory(5)->create(); // 🟢 buat 10 data dummy
    }
}
