<?php

namespace App\Imports;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class UsersImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        $user = User::create([
            'name' => $row['nama'],
            'email' => $row['email'],
            'phone_number' => $row['nomor_hp'] ?? null,
            'password' => Hash::make($row['password'] ?? '12345678'),
        ]);

        if (!empty($row['role'])) {
            $role = Role::firstOrCreate(['name' => strtolower($row['role'])]);
            $user->assignRole($role);
        }

        return $user;
    }
}
