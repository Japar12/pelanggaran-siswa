<?php

namespace App\Filament\Resources\NotificationLogs\Tables;

use Filament\Tables\Table;
use Filament\Actions\ViewAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;

class NotificationLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->poll('60s')
            ->columns([
                 TextColumn::make('violation.description')
                    ->label('Deskripsi Pelanggaran')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('user.name')
                    ->label('Nama User')
                    ->sortable()
                    ->searchable(),

                BadgeColumn::make('channel')
                    ->label('Kanal')
                    ->colors([
                        'info' => fn($state) => $state === 'web',
                        'success' => fn($state) => $state === 'email',
                        'warning' => fn($state) => $state === 'whatsapp',
                        'gray' => fn($state) => !in_array($state, ['web', 'email', 'whatsapp']),
                    ])
                    ->icons([
                        'heroicon-o-bell' => 'web',
                        'heroicon-o-envelope' => 'email',
                        'heroicon-o-chat-bubble-left-ellipsis' => 'whatsapp',
                    ])
                    ->sortable(),

                BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'success' => 'success',
                        'failed' => 'danger',
                    ])
                    ->icons([
                        'heroicon-o-check-circle' => 'success',
                        'heroicon-o-x-circle' => 'failed',
                    ])
                    ->sortable(),

                TextColumn::make('message')
                    ->label('Pesan / Keterangan')
                    ->limit(60)
                    ->wrap()
                    ->tooltip(fn($record) => $record->message),

                TextColumn::make('created_at')
                    ->label('Dikirim Pada')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->description(fn($record) => $record->created_at->diffForHumans()),
            ])
            ->filters([
                 SelectFilter::make('channel')
                    ->label('Kanal')
                    ->options([
                        'web' => 'Web',
                        'email' => 'Email',
                        'whatsapp' => 'WhatsApp',
                    ]),
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'success' => 'Berhasil',
                        'failed' => 'Gagal',
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),

            ])
             ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('Belum Ada Riwayat Notifikasi')
            ->emptyStateDescription('Semua pengiriman notifikasi akan tercatat di sini.');

    }
}
