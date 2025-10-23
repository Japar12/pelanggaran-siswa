<?php

namespace App\Filament\Widgets\Admin;

use App\Models\Violation;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class ViolationStatsOverview extends BaseWidget
{
    protected ?string $heading = 'Statistik Laporan Pelanggaran';

    protected function getStats(): array
    {
        $total = Violation::count();
        $approved = Violation::where('status', 'approved')->count();
        $pending = Violation::where('status', 'pending')->count();
        $rejected = Violation::where('status', 'rejected')->count();

        return [
            Stat::make('Total Laporan', $total)
                ->description('Laporan pelanggaran')
                ->icon('heroicon-o-document-text')
                ->color('primary'),

            Stat::make('Approved', $approved)
                ->description('Telah disetujui')
                ->icon('heroicon-o-check-circle')
                ->color('success'),

            Stat::make('Pending', $pending)
                ->description('Menunggu verifikasi')
                ->icon('heroicon-o-clock')
                ->color('warning'),

            Stat::make('Rejected', $rejected)
                ->description('Telah ditolak')
                ->icon('heroicon-o-x-circle')
                ->color('danger'),
        ];
    }

    // tampil separuh lebar biar bisa sejajar sama chart
    public function getColumnSpan(): int|string|array
    {
        return 12;
    }
}
