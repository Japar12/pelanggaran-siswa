<?php

namespace App\Filament\Resources\ClassRooms\Tables;

use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Schemas\Components\View;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;

class ClassRoomsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Nama Kelas')->searchable(),
                TextColumn::make('grade')->label('Tingkat/Kelas')->searchable(),
                TextColumn::make('teacher.name')->label('Wali Kelas')->searchable(),
            ])
            ->filters([
                SelectFilter::make('grade')->label('Tingkat/Kelas')->options([
                    '10' => '10',
                    '11' => '11',
                    '12' => '12',
                ])
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
                ViewAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
