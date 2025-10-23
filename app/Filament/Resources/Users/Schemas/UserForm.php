<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Schemas\Schema;
use Spatie\Permission\Models\Role;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DateTimePicker;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                 TextInput::make('name')
                    ->label('Nama Lengkap')
                    ->required(),

                TextInput::make('email')
                    ->label('Email (gunakan NISN@email.com untuk siswa)')
                    ->email()
                    ->unique(ignoreRecord: true)
                    ->required(fn(string $operation): bool => $operation === 'create'), // ✅ hanya wajib di create

                TextInput::make('phone_number')
                    ->label('Nomor WhatsApp')
                    ->placeholder('contoh: 081234567890')
                    ->rule('regex:/^(?:\+62|62|0)[0-9]{9,15}$/') // validasi format umum
                    ->required(fn(string $operation): bool => $operation === 'create') // ✅ hanya wajib saat create
                    ->dehydrateStateUsing(function ($state) {
                        if (!$state) return null;

                        // Hapus non-digit
                        $clean = preg_replace('/\D/', '', $state);

                        // Ubah 08... jadi 628...
                        if (str_starts_with($clean, '08')) {
                            $clean = '628' . substr($clean, 2);
                        }

                        // Tambahkan 62 kalau belum ada
                        if (!str_starts_with($clean, '62')) {
                            $clean = '62' . ltrim($clean, '0');
                        }

                        return $clean;
                    })
                    ->suffixIcon('heroicon-o-phone'),

                TextInput::make('password')
                    ->label('Password')
                    ->password()
                    ->revealable()
                    // ✅ hanya hash kalau diisi
                    ->dehydrateStateUsing(fn($state) => filled($state) ? bcrypt($state) : null)
                    ->dehydrated(fn($state) => filled($state))
                    ->required(fn(string $operation): bool => $operation === 'create'),

               Select::make('role')
    ->label('Role Pengguna')
    ->options([
        'admin' => 'Admin',
        'guru' => 'Guru',
        'siswa' => 'Siswa',
        'ortu' => 'Orang Tua',
    ])
    ->required(fn(string $operation): bool => $operation === 'create') // ✅ hanya wajib saat create
    ->default(fn($record) => $record?->roles->first()?->name) // ✅ isi default dari role yang sudah ada
    ->afterStateHydrated(function ($set, $record) {
        // ✅ isi form saat edit agar tampil value role lama
        if ($record && $record->roles->isNotEmpty()) {
            $set('role', $record->roles->first()->name);
        }
    })
    ->afterStateUpdated(function ($state, $record) {
        // ✅ sinkronisasi Spatie role agar tidak ganda
        if ($record) {
            $record->syncRoles([$state]);
        }
    }),

        ]);
    }
}
