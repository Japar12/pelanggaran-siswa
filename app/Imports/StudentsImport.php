<?php

namespace App\Imports;

use App\Models\Student;
use App\Models\User;
use App\Models\ClassRoom;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class StudentsImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        $classRoom = ClassRoom::firstOrCreate(['name' => $row['kelas'] ?? 'Tanpa Kelas']);

        $parent = null;
        if (!empty($row['nama_ortu'])) {
            $parent = User::firstOrCreate(
                ['name' => $row['nama_ortu']],
                ['email' => $row['email_ortu'] ?? null, 'password' => bcrypt('12345678')]
            );
            $parent->assignRole('ortu');
        }

        return new Student([
            'name' => $row['nama'],
            'nisn' => $row['nisn'],
            'gender' => strtolower($row['jenis_kelamin']) === 'l' ? 'L' : 'P',
            'class_room_id' => $classRoom->id,
            'parent_user_id' => $parent?->id,
            'total_points' => $row['total_poin'] ?? 0,
        ]);
    }
}
