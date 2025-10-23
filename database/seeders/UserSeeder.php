<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // pastikan role sudah dibuat
        $roles = ['admin', 'guru', 'ortu', 'siswa'];
        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role]);
        }

        // Admin
        $admin = User::create([
            'name' => 'Admin Sekolah',
            'email' => 'admin@sekolah.com',
            'password' => bcrypt('admin123'),
        ]);
        $admin->assignRole('admin');

        // Guru
        $guru = User::create([
            'name' => 'Guru Contoh',
            'email' => 'guru@sekolah.com',
            'password' => bcrypt('guru123'),
        ]);
        $guru->assignRole('guru');

        // Orang Tua
        $ortu = User::create([
            'name' => 'Orang Tua Contoh',
            'email' => 'ortu@sekolah.com',
            'password' => bcrypt('ortu123'),
        ]);
        $ortu->assignRole('ortu');

        // Siswa
        $siswa = User::create([
            'name' => 'Siswa Contoh',
            'email' => 'siswa@sekolah.com',
            'password' => bcrypt('siswa123'),
        ]);
        $siswa->assignRole('siswa');
    }
}
