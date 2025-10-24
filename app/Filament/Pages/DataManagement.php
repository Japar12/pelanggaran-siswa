<?php

namespace App\Filament\Pages;

use BackedEnum;
use UnitEnum;
use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Support\Icons\Heroicon;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;
use Maatwebsite\Excel\Facades\Excel;
use Exception;
use App\Models\ClassRoom;
use App\Exports\UsersExport;
use App\Exports\StudentsExport;
use App\Exports\ViolationsExport;
use App\Imports\UsersImport;
use App\Imports\StudentsImport;

class DataManagement extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::ArrowDownTray;
    protected static string|UnitEnum|null $navigationGroup = 'Sistem';
    protected static ?string $navigationLabel = 'Data Management';
    protected static ?string $title = 'Manajemen Data';
    protected static bool $shouldRegisterNavigation = true;
    protected string $view = 'filament.pages.data-management';

    public function getHeading(): string
    {
        return '⚙️ Manajemen Data Sistem';
    }

    public function getSubheading(): ?string
    {
        return 'Kelola export, import, format, dan backup data pengguna, siswa, serta pelanggaran.';
    }

    // Header actions (ditampilkan di atas)
    protected function getHeaderActions(): array
    {
        return [
            Action::make('backupDatabase')
                ->label('Backup Database')
                ->icon(Heroicon::CloudArrowDown)
                ->color('danger')
                ->visible(fn() => auth()->user()->hasRole('admin'))
                ->requiresConfirmation()
                ->modalHeading('Konfirmasi Backup Database')
                ->modalDescription('File .sql akan disimpan di folder storage/app/backups/ dan otomatis diunduh.')
                ->modalSubmitActionLabel('Mulai Backup')
                ->action(function () {
                    return $this->performDatabaseBackup();
                }),
        ];
    }

    // Actions untuk konten (ditampilkan di body/content)
    public function getUserActions(): array
    {
        return [
            Action::make('exportUsers')
                ->label('Export Users')
                ->icon(Heroicon::ArrowDownTray)
                ->color('info')
                ->visible(fn() => auth()->user()->hasRole('admin'))
                ->action(fn() => Excel::download(new UsersExport, 'data_users.xlsx')),

            Action::make('importUsers')
                ->label('Import Users')
                ->icon(Heroicon::ArrowUpTray)
                ->color('primary')
                ->visible(fn() => auth()->user()->hasRole('admin'))
                ->form([
                    FileUpload::make('file')
                        ->label('Pilih File Excel (.xlsx)')
                        ->helperText('Gunakan template resmi agar struktur kolom sesuai.')
                        ->acceptedFileTypes([
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            'application/vnd.ms-excel',
                        ])
                        ->required(),
                ])
                ->action(fn($data) => Excel::import(new UsersImport, $data['file'])),

            Action::make('downloadUserTemplate')
                ->label('Download Template')
                ->icon(Heroicon::DocumentText)
                ->color('gray')
                ->visible(fn() => auth()->user()->hasRole('admin'))
                ->action(fn() => response()->download(public_path('templates/users_import_template.xlsx'))),
        ];
    }

    public function getStudentActions(): array
    {
        return [
            Action::make('exportStudents')
                ->label('Export Siswa')
                ->icon(Heroicon::ArrowDownTray)
                ->color('success')
                ->visible(fn() => auth()->user()->hasRole('admin'))
                ->action(fn() => Excel::download(new StudentsExport, 'data_siswa.xlsx')),

            Action::make('importStudents')
                ->label('Import Siswa')
                ->icon(Heroicon::ArrowUpTray)
                ->color('primary')
                ->visible(fn() => auth()->user()->hasRole('admin'))
                ->form([
                    FileUpload::make('file')
                        ->label('Pilih File Excel (.xlsx)')
                        ->helperText('Gunakan template resmi agar struktur kolom sesuai.')
                        ->acceptedFileTypes([
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            'application/vnd.ms-excel',
                        ])
                        ->required(),
                ])
                ->action(fn($data) => Excel::import(new StudentsImport, $data['file'])),

            Action::make('downloadStudentTemplate')
                ->label('Download Template')
                ->icon(Heroicon::DocumentText)
                ->color('gray')
                ->visible(fn() => auth()->user()->hasRole('admin'))
                ->action(fn() => response()->download(public_path('templates/students_import_template.xlsx'))),
        ];
    }

    public function getViolationActions(): array
    {
        return [
            Action::make('exportViolations')
                ->label('Export Pelanggaran (Filter)')
                ->icon(Heroicon::DocumentArrowDown)
                ->color('warning')
                ->visible(fn() => auth()->user()->hasAnyRole(['admin', 'guru']))
                ->form([
                    DatePicker::make('start_date')
                        ->label('Tanggal Mulai')
                        ->default(now()->startOfMonth())
                        ->required(),
                    DatePicker::make('end_date')
                        ->label('Tanggal Selesai')
                        ->default(now())
                        ->required(),
                    Select::make('class_room_id')
                        ->label('Kelas')
                        ->options(ClassRoom::pluck('name', 'id'))
                        ->placeholder('Semua Kelas')
                        ->searchable(),
                ])
                ->action(function (array $data) {
                    return $this->performViolationsExport($data);
                }),
        ];
    }

    // Helper methods
    private function performDatabaseBackup()
    {
        $database = env('DB_DATABASE');
        $username = env('DB_USERNAME');
        $password = env('DB_PASSWORD');
        $host = env('DB_HOST', '127.0.0.1');
        $backupPath = storage_path('app/backups');
        $filename = 'backup-' . $database . '-' . now()->format('Y-m-d_H-i-s') . '.sql';
        $filePath = $backupPath . '/' . $filename;

        if (!file_exists($backupPath)) {
            mkdir($backupPath, 0777, true);
        }

        exec("mysqldump --user={$username} --password=\"{$password}\" --host={$host} {$database} > \"{$filePath}\"");

        return Response::download($filePath)->deleteFileAfterSend(false);
    }

    private function performViolationsExport(array $data)
    {
        $filename = 'pelanggaran_filtered_' . now()->format('Y-m-d_H-i-s') . '.xlsx';

        $query = \App\Models\Violation::with(['student.class_room', 'createdBy'])
            ->whereBetween('date', [$data['start_date'], $data['end_date']]);

        if (!empty($data['class_room_id'])) {
            $query->whereHas('student', fn($q) => $q->where('class_room_id', $data['class_room_id']));
        }

        if (auth()->user()->hasRole('guru')) {
            $query->where('created_by', auth()->id());
        }

        return Excel::download(new ViolationsExport($query->get()), $filename);
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->check() && auth()->user()->hasAnyRole(['admin', 'guru']);
    }
}
