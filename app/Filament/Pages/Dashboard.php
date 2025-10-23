<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Support\Icons\Heroicon;

class Dashboard extends BaseDashboard
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::Home;
    protected static ?string $navigationLabel = 'Dashboard';
    protected static ?string $title = 'Dashboard Pelanggaran Siswa';
    protected static bool $shouldRegisterNavigation = true;

    public static function canAccess(): bool
    {
        return auth()->check();
    }

    protected function getHeaderWidgets(): array
    {
        $user = auth()->user();

        if ($user->hasRole('admin')) {
            return [
                \App\Filament\Widgets\Admin\UsersStatsOverview::class,
                \App\Filament\Widgets\Admin\ViolationStatsOverview::class,
                \App\Filament\Widgets\Admin\TopStudentsTable::class,
            ];
        }

        if ($user->hasRole('guru')) {
            return [
                // \App\Filament\Widgets\GuruViolationOverview::class,
                // \App\Filament\Widgets\GuruViolationsTrendChart::class,
            ];
        }

        if ($user->hasRole('siswa')) {
            return [
                // \App\Filament\Widgets\SiswaViolationOverview::class,
                // \App\Filament\Widgets\SiswaViolationsHistoryChart::class,
            ];
        }

        if ($user->hasRole('ortu')) {
            return [
                // \App\Filament\Widgets\OrtuViolationOverview::class,
                // \App\Filament\Widgets\AnakViolationStatusChart::class,
            ];
        }

        return [];
    }

    public function getHeaderWidgetsColumns(): int|array
    {
       return [
        'default' => 1, // tampil 1 kolom di layar kecil
        'md' => 2,      // tampil 2 kolom di layar medium ke atas
        'xl' => 3,      // kalau mau, bisa jadi 3 kolom di layar besar
    ];
    }

    // 🧠 Tambahkan ini biar isi dashboard tidak tampil dua kali
    public function getWidgets(): array
    {
        return [];
    }
}
