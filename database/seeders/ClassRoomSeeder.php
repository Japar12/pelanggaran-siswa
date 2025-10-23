<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\ClassRoom;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ClassRoomSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ClassRoom::factory(5)->create(); // Buat 5 kelas acak
    }
}
