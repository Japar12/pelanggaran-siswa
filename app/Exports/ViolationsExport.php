<?php

namespace App\Exports;

use App\Models\Violation;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ViolationsExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    public function collection()
    {
        // Ambil data dengan relasi student + class_room + createdBy
        $query = Violation::with(['student.class_room', 'createdBy']);

        // Guru hanya boleh export data yang ia buat
        if (auth()->user()->hasRole('guru')) {
            $query->where('created_by', auth()->id());
        }

        return $query->orderBy('date', 'desc')->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Tanggal',
            'Nama Siswa',
            'NISN',
            'Kelas',
            'Guru Pelapor',
            'Kategori',
            'Poin',
            'Status',
            'Deskripsi',
        ];
    }

    public function map($violation): array
    {
        return [
            $violation->id,
            $violation->date ? $violation->date->format('Y-m-d') : '-',
            $violation->student->name ?? '-',
            $violation->student->nisn ?? '-',
            $violation->student->class_room->name ?? '-',
            $violation->createdBy->name ?? '-',
            $violation->category ?? '-',
            $violation->points ?? 0,
            ucfirst($violation->status ?? '-'),
            $violation->description ?? '-',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Bold untuk baris header
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
