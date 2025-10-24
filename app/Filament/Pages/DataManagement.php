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


    public function getHeading(): string
    {
        return '⚙️ Manajemen Data Sistem';
    }

    public function getSubheading(): ?string
    {
        return 'Gunakan tombol di bawah untuk melakukan export, import, dan backup data.';
    }

    public function getHeaderActions(): array
    {
        return [
            // ==========================
            // ==== USERS EXPORT/IMPORT
            // ==========================
            Action::make('exportUsers')
                ->label('Export Users')
                ->icon(Heroicon::ArrowDownTray)
                ->color('info')
                ->visible(fn () => auth()->user()->hasRole('admin'))
                ->action(fn () => Excel::download(new UsersExport, 'data_users.xlsx')),

            Action::make('importUsers')
                ->label('Import Users')
                ->icon(Heroicon::ArrowUpTray)
                ->color('primary')
                ->visible(fn () => auth()->user()->hasRole('admin'))
                ->form([
                    FileUpload::make('file')
                        ->label('Pilih File Excel (.xlsx)')
                        ->acceptedFileTypes([
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            'application/vnd.ms-excel',
                        ])
                        ->required(),
                ])
                ->action(function (array $data) {
                    Excel::import(new UsersImport, $data['file']);
                    Notification::make()
                        ->title('✅ Import Users berhasil!')
                        ->success()
                        ->send();
                }),

            // ==========================
            // ==== STUDENTS EXPORT/IMPORT
            // ==========================
            Action::make('exportStudents')
                ->label('Export Siswa')
                ->icon(Heroicon::ArrowDownTray)
                ->color('success')
                ->visible(fn () => auth()->user()->hasRole('admin'))
                ->action(fn () => Excel::download(new StudentsExport, 'data_siswa.xlsx')),

            Action::make('importStudents')
                ->label('Import Siswa')
                ->icon(Heroicon::ArrowUpTray)
                ->color('primary')
                ->visible(fn () => auth()->user()->hasRole('admin'))
                ->form([
                    FileUpload::make('file')
                        ->label('Pilih File Excel (.xlsx)')
                        ->acceptedFileTypes([
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            'application/vnd.ms-excel',
                        ])
                        ->required(),
                ])
                ->action(function (array $data) {
                    Excel::import(new StudentsImport, $data['file']);
                    Notification::make()
                        ->title('✅ Import Siswa berhasil!')
                        ->success()
                        ->send();
                }),

            // ==========================
            // ==== VIOLATION EXPORT (FILTER)
            // ==========================
            Action::make('exportViolations')
                ->label('Export Pelanggaran (Filter)')
                ->icon(Heroicon::DocumentArrowDown)
                ->color('warning')
                ->visible(fn () => auth()->user()->hasAnyRole(['admin', 'guru']))
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
                    $filename = 'pelanggaran_filtered_' . now()->format('Y-m-d_H-i-s') . '.xlsx';

                    $query = \App\Models\Violation::with(['student.class_room', 'createdBy'])
                        ->whereBetween('date', [$data['start_date'], $data['end_date']]);

                    if (!empty($data['class_room_id'])) {
                        $query->whereHas('student', fn ($q) =>
                            $q->where('class_room_id', $data['class_room_id'])
                        );
                    }

                    if (auth()->user()->hasRole('guru')) {
                        $query->where('created_by', auth()->id());
                    }

                    $violations = $query->orderBy('date', 'desc')->get();

                    return Excel::download(new ViolationsExport($violations), $filename);
                }),

            // ==========================
            // ==== BACKUP DATABASE
            // ==========================
            Action::make('backupDatabase')
                ->label('Backup Database (.sql)')
                ->icon(Heroicon::CloudArrowDown)
                ->color('danger')
                ->visible(fn () => auth()->user()->hasRole('admin'))
                ->requiresConfirmation()
                ->modalHeading('Konfirmasi Backup Database')
                ->modalDescription('File akan disimpan di folder storage/app/backups/ dan otomatis diunduh.')
                ->modalSubmitActionLabel('Mulai Backup')
                ->action(function () {
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

                    try {
                        // coba pakai mysqldump
                        $command = "mysqldump --user={$username} --password=\"{$password}\" --host={$host} {$database} > \"{$filePath}\"";
                        exec($command, $output, $result);

                        if ($result !== 0) {
                            // fallback native
                            $tables = DB::select('SHOW TABLES');
                            $sqlDump = "CREATE DATABASE IF NOT EXISTS `$database`;\nUSE `$database`;\n\n";

                            foreach ($tables as $table) {
                                $tableName = array_values((array) $table)[0];
                                $create = DB::select("SHOW CREATE TABLE `$tableName`")[0]->{'Create Table'};
                                $sqlDump .= $create . ";\n\n";
                                $rows = DB::table($tableName)->get();
                                foreach ($rows as $row) {
                                    $values = array_map(fn ($v) => $v === null ? 'NULL' : DB::getPdo()->quote($v), (array) $row);
                                    $sqlDump .= "INSERT INTO `$tableName` VALUES (" . implode(',', $values) . ");\n";
                                }
                                $sqlDump .= "\n\n";
                            }
                            file_put_contents($filePath, $sqlDump);
                        }

                        // kirim notifikasi
                        Notification::make()
                            ->title('✅ Backup Database Berhasil')
                            ->body("File disimpan di storage/app/backups/{$filename} dan otomatis diunduh.")
                            ->success()
                            ->send();

                        // langsung download file ke browser
                        return Response::download($filePath)->deleteFileAfterSend(false);

                    } catch (Exception $e) {
                        Notification::make()
                            ->title('❌ Backup Gagal')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->check() && auth()->user()->hasAnyRole(['admin', 'guru']);
    }
}
