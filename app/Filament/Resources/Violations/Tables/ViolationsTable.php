<?php

namespace App\Filament\Resources\Violations\Tables;

use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Tables\Filters\Filter;
use Filament\Actions\BulkActionGroup;
use Filament\Schemas\Components\View;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;

class ViolationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
         ->modifyQueryUsing(function (Builder $query) {
            if (request()->filled('student_id')) {
                $query->where('student_id', request('student_id'));
            }
        })
            ->columns([
                TextColumn::make('student.name')->label('Nama Siswa')->sortable()->searchable(),
                TextColumn::make('createdBy.name')
                    ->label('Dibuat Oleh')
                    ->visible(fn() => auth()->user()?->hasRole('admin')),
             TextColumn::make('student.class_room.name')
                    ->label('Kelas')
                    ->getStateUsing(fn($record) => $record->student?->class_room?->name ?? '-'),
                TextColumn::make('description')->label('Pelanggaran')->searchable(),
                TextColumn::make('status')
    ->label('Status')
    ->badge()
    ->colors([
        'warning' => 'pending',
        'success' => 'approved',
        'danger' => 'rejected',
    ])
    ->visible(fn() => auth()->user()?->hasAnyRole(['admin', 'guru']))
    ->sortable(),

                TextColumn::make('points')->label('Poin')->sortable(),
                TextColumn::make('date')->label('Tanggal')->date(),
                TextColumn::make('student.total_points')->label('Total Poin Siswa')->sortable(),

            ])
            ->filters([
                            SelectFilter::make('class_room')
                    ->label('Kelas')
                    ->relationship('student.class_room', 'name')
                    ->visible(fn() => auth()->user()?->hasAnyRole(['admin'])),


                Filter::make('date')
                    ->form([
                        DatePicker::make('from')->label('Dari'),
                        DatePicker::make('until')->label('Sampai'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['from'], fn($q) => $q->whereDate('date', '>=', $data['from']))
                            ->when($data['until'], fn($q) => $q->whereDate('date', '<=', $data['until']));
        }),
            ])
            ->defaultSort('date', 'desc') // 🟢 urutkan berdasarkan tanggal terbaru
->recordActions([
    ActionGroup::make([
 ViewAction::make(),

    EditAction::make()
        ->visible(fn ($record) =>
            auth()->user()->hasRole('admin') ||
            (auth()->user()->hasRole('guru') && $record->created_by === auth()->id())
        ),

    DeleteAction::make()
        ->visible(fn ($record) =>
            auth()->user()->hasRole('admin') ||
            (auth()->user()->hasRole('guru') &&
             $record->created_by === auth()->id() &&
             $record->status !== 'approved')
        ),

    Action::make('approve')
        ->label('Setujui')
        ->icon('heroicon-o-check-circle')
        ->color('success')
        ->requiresConfirmation()
        ->visible(fn ($record) =>
            auth()->user()->hasRole('admin') && $record->status === 'pending'
        )
        ->action(fn ($record) => $record->update(['status' => 'approved'])),

    Action::make('reject')
        ->label('Tolak')
        ->icon('heroicon-o-x-circle')
        ->color('danger')
        ->requiresConfirmation()
        ->visible(fn ($record) =>
            auth()->user()->hasRole('admin') && $record->status === 'pending'
        )
        ->action(fn ($record) => $record->update(['status' => 'rejected'])),
])
    ])




            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
