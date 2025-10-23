<?php

namespace App\Filament\Widgets\Admin;

use Filament\Tables;
use Filament\Actions\Action;
use App\Models\Student;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\Students\StudentResource;
use Filament\Widgets\TableWidget as BaseWidget;

/**
 * Widget: Top Students
 * Versi ringan — menampilkan 10 siswa dengan poin tertinggi + persentase dari max point tetap.
 */
class TopStudentsTable extends BaseWidget
{
    protected static ?string $heading = 'Top 10 Siswa dengan Poin Tertinggi';
    protected static bool $isLazy = true; // hanya render saat dibuka

    // 🎯 Set nilai maksimum poin langsung di kode
    protected int $maxPoints = 300;

    public function getColumnSpan(): int|string|array
    {
        return 12;
    }

    protected function getTableQuery(): Builder
    {
        return Student::select(['id', 'name', 'gender', 'class_room_id'])
            ->with(['class_room:id,name'])
            ->withSum('violations', 'points')
            ->orderByDesc('violations_sum_points')
            ->limit(10);
    }

    protected function getTableColumns(): array
    {
        $max = $this->maxPoints; // ambil nilai tetap

        return [
            Tables\Columns\TextColumn::make('name')
                ->label('Nama Siswa')
                ->sortable()
                ->searchable(),

            Tables\Columns\TextColumn::make('gender')
                ->label('Gender')
                ->badge()
                ->color(fn (string $state) => $state === 'Laki-laki' ? 'info' : 'pink'),

            Tables\Columns\TextColumn::make('class_room.name')
                ->label('Kelas')
                ->default('-'),

            Tables\Columns\TextColumn::make('violations_sum_points')
                ->label('Total Poin')
                ->sortable()
                ->badge()
                ->color(fn ($state) => match (true) {
                    $state >= 200 => 'danger',
                    $state >= 100 => 'warning',
                    default => 'success',
                }),

            Tables\Columns\TextColumn::make('percentage')
                ->label('Persentase (%)')
                ->state(fn (Student $record) =>
                    number_format(($record->violations_sum_points ?? 0) / $max * 100, 1)
                )
                ->suffix('%')
                ->color(fn ($state) => match (true) {
                    $state >= 75 => 'danger',
                    $state >= 50 => 'warning',
                    default => 'success',
                }),
        ];
    }

    protected function getTableActions(): array
    {
        return [
            Action::make('Lihat Detail')
                ->label('Lihat')
                ->icon('heroicon-o-eye')
                ->color('info')
                ->url(fn (Student $record) =>
                    StudentResource::getUrl('view', ['record' => $record])
                ),
        ];
    }
}
