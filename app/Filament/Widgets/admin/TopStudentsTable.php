<?php

namespace App\Filament\Widgets\Admin;

use Filament\Tables;
use App\Models\Student;
use Filament\Actions\Action;
use Illuminate\Database\Eloquent\Builder;
use Filament\Widgets\TableWidget as BaseWidget;
use App\Filament\Resources\Students\StudentResource;
use App\Filament\Resources\Violations\ViolationResource;

class TopStudentsTable extends BaseWidget
{
    protected static ?string $heading = 'Top 10 Siswa dengan Poin Tertinggi';
    protected static bool $isLazy = true;

    protected int $maxPoints;

    public function boot(): void
    {
        $this->maxPoints = (int) env('MAX_VIOLATION_POINTS', 300);
    }

    public function getColumnSpan(): int|string|array
    {
        return 12;
    }

    protected function getTableQuery(): Builder
    {
         // 🎯 Hanya hitung poin dari pelanggaran yang disetujui
        return Student::select(['id', 'name', 'gender', 'class_room_id'])
            ->with(['class_room:id,name'])
            ->withSum(
                ['violations as violations_sum_points' => function ($query) {
                    $query->where('status', 'approved'); // hanya approved
                }],
                'points'
            )
            ->orderByDesc('violations_sum_points')
            ->limit(10);
    }

    protected function getTableColumns(): array
    {
        $max = $this->maxPoints;

        return [
            Tables\Columns\TextColumn::make('name')
                ->label('Nama Siswa')
                ->searchable(),

            Tables\Columns\TextColumn::make('gender')
                ->label('Gender')
                ->badge()
                ->searchable()
                ->default('-')
                ->color(fn (string|null $state) => match ($state) {
                    'Laki-laki' => 'info',
                    'Perempuan' => 'pink',
                    default => 'gray',
                }),

            Tables\Columns\TextColumn::make('class_room.name')
                ->label('Kelas')
                ->searchable()
                ->default('-'),

            Tables\Columns\TextColumn::make('violations_sum_points')
                ->label('Total Poin')
                ->default(0)
                ->badge()
                ->color(fn ($state) => match (true) {
                    $state >= 200 => 'danger',
                    $state >= 100 => 'warning',
                    default => 'success',
                }),

            Tables\Columns\TextColumn::make('percentage')
                ->label('Persentase (%)')
                ->state(fn (Student $record) =>
                    number_format((($record->violations_sum_points ?? 0) / $max) * 100, 1)
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
                    ViolationResource::getUrl('index', ['student_id' => $record->id])
                ),
                
        ];
    }
}
