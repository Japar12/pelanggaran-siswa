<?php

namespace App\Filament\Resources\ClassRooms\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;

class ClassRoomForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
               TextInput::make('name')
                ->label('Nama Kelas')
                ->placeholder('XII RPL 1')
                ->required(),

                TextInput::make('grade')
                    ->label('Tingkat/Kelas')
                    ->placeholder('12')
                    ->required(),

             Select::make('teacher_id')
                ->label('Wali Kelas')
                ->relationship(
                    'teacher',
                    'name',
                    fn($query) => $query->role('guru')
                )
                ->searchable()
                ->required(),

            ]);
    }
}
