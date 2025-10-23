<?php

namespace App\Filament\Resources\Students\Tables;

use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;

class StudentsTable
{
    public static function configure(Table $table): Table
    {
        $user = auth()->user();

        // Default actions
        $recordActions = [ViewAction::make()];
        $toolbarActions = [];

        // Kalau admin, boleh semua
        if ($user && $user->hasRole('admin')) {
            $recordActions = [
                EditAction::make(),
                DeleteAction::make(),
                ViewAction::make(),
            ];

            $toolbarActions = [
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ];
        }

        return $table
            ->columns([
                TextColumn::make('nisn')->label('NISN')->searchable(),
                TextColumn::make('name')->label('Nama Siswa')->searchable(),
                TextColumn::make('gender')->label('gender')->searchable(),
                TextColumn::make('class_room.name')->label('Kelas')->searchable(),
            ])
            ->filters([
                SelectFilter::make('class_room_id')
                    ->label('Kelas')
                    ->relationship('class_room', 'name'),
            ])
            ->recordActions($recordActions)
            ->toolbarActions($toolbarActions);
    }
}
