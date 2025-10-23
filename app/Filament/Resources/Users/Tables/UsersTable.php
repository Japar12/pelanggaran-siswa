<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\DeleteAction;
use Spatie\Permission\Models\Role;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                    TextColumn::make('name')->label('Nama'),
                    TextColumn::make('phone_number') // ✅ tambahan
        ->label('Nomor WA')
        ->sortable()
        ->searchable()
        ->formatStateUsing(fn($state) => $state ? $state : '-'),
            TextColumn::make('email')->label('Email'),

            // 🟢 Badge warna berdasarkan role
           TextColumn::make('roles.name')
    ->label('Role')
    ->badge()
    ->colors([
        'danger' => 'admin',
        'info' => 'guru',
        'success' => 'siswa',
        'gray' => 'ortu',
    ])
    ->formatStateUsing(fn ($state) => is_array($state) ? implode(', ', $state) : $state),

            TextColumn::make('created_at')
                ->label('Dibuat')
                ->dateTime()
                ->sortable(),
            ])
            ->filters([
                 // 🎯 Filter berdasarkan role
            SelectFilter::make('roles')
                ->label('Filter Role')
                ->options(Role::pluck('name', 'name')->toArray())
                ->query(function ($query, $data) {
                    if (!empty($data['value'])) {
                        $query->whereHas('roles', fn($q) => $q->where('name', $data['value']));
                    }
                }),
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
