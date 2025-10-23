<?php

namespace App\Filament\Widgets\Admin;

use App\Models\User;
use App\Models\Student;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class UsersStatsOverview extends BaseWidget
{
    // ❌ sebelumnya static, ubah ke non-static
    protected ?string $heading = 'Statistik Pengguna Sistem';

    protected function getCards(): array
    {
        return [
            Stat::make('Total Admin', User::role('admin')->count())
                ->icon('heroicon-o-user-circle')
                ->color('success'),

            Stat::make('Total Guru', User::role('guru')->count())
                ->icon('heroicon-o-academic-cap')
                ->color('info'),

            Stat::make('Total Siswa', Student::count())
                ->icon('heroicon-o-user-group')
                ->color('warning'),

            Stat::make('Total Orang Tua', User::role('ortu')->count())
                ->icon('heroicon-o-home')
                ->color('gray'),
        ];
    }
    public function getColumnSpan(): int|string|array
{
    return 12;
}

}
