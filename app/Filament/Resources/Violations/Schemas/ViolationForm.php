<?php

namespace App\Filament\Resources\Violations\Schemas;

use App\Models\User;
use App\Models\Student;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;

class ViolationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('student_id')
                    ->label('Nama Siswa')

                    ->relationship('student', 'name')
                    ->searchable()
                    ->live()
                    ->required(),


                TextInput::make('description')
                    ->label('Jenis Pelanggaran')
                    ->required(),

                TextInput::make('points')
                    ->label('Poin')
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(100)
                    ->required(),

                DatePicker::make('date')
                    ->label('Tanggal Pelanggaran')
                    ->default(now())
                    ->required(),


                Radio::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Menunggu Verifikasi',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                    ])
                    ->default('pending')
                    ->visible(fn () => auth()->user()?->hasRole('admin'))
            ]);
    }
}

