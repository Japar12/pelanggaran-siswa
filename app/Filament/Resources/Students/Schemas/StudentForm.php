<?php

namespace App\Filament\Resources\Students\Schemas;

use App\Models\User;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;

class StudentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nisn')
                ->label('NISN')
                ->required(),

            TextInput::make('name')
                ->label('Nama Siswa')
                ->required(),

            Select::make('class_room_id')
                ->label('Kelas')
                ->relationship('class_room', 'name')
                ->searchable()
                ->required(),

            Select::make('gender')
                ->label('Jenis Kelamin')
                ->searchable()
                ->options([
                    'L' => 'Laki-laki',
                    'P' => 'Perempuan',
                ])
                ->required(),

            // 🔹 Hubungkan siswa ke akun user dengan role siswa
            Select::make('user_id')
                ->label('Akun Siswa')
                ->options(function () {
                    return User::role('siswa')->pluck('name', 'id');
                })
                ->searchable()
                ->preload()
                ->nullable()
                ->helperText('Pilih akun user untuk siswa ini (role: siswa)'),

            // 🔹 Hubungkan siswa ke orang tua (bisa punya beberapa anak)
            Select::make('parent_user_id')
                ->label('Orang Tua')
                ->options(function () {
                    return User::role('ortu')->pluck('name', 'id');
                })
                ->searchable()
                ->preload()
                ->nullable()
                ->helperText('Pilih akun user orang tua (role: ortu)'),
                ]);
    }
}
