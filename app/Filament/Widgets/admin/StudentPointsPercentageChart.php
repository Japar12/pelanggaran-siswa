<?php

namespace App\Filament\Widgets\Admin;

use App\Models\Student;
use Filament\Widgets\ChartWidget;

class StudentPointsPercentageChart extends ChartWidget
{
    protected ?string $heading = 'Student Points Percentage Chart';

    protected function getData(): array
    {
        $maxPoints = 100; // bisa ubah di setting admin
        $students = Student::withSum('violations', 'points')->get();

        $labels = $students->pluck('name')->toArray();
        $percentages = $students->map(fn ($s) => min(100, ($s->violations_sum_points / $maxPoints) * 100))->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Persentase Poin (%)',
                    'data' => $percentages,
                    'borderColor' => '#f59e0b',
                    'backgroundColor' => '#fde68a',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
    public function getColumnSpan(): int|string|array
{
    return [
           'default' => 1,
        'md' => 2,  // penuh pada grid 2 kolom
        'xl' => 2,
    ];
}

}
