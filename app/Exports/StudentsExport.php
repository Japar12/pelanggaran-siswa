<?php

namespace App\Exports;

use App\Models\Student;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class StudentsExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return Student::with(['classRoom', 'parentUser'])->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Nama',
            'NISN',
            'Jenis Kelamin',
            'Kelas',
            'Orang Tua',
            'Total Poin',
        ];
    }

    public function map($student): array
    {
        return [
            $student->id,
            $student->name,
            $student->nisn,
            $student->gender === 'L' ? 'Laki-laki' : 'Perempuan',
            $student->classRoom->name ?? '-',
            $student->parentUser->name ?? '-',
            $student->total_points,
        ];
    }
}
