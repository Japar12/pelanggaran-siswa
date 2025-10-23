<?php

namespace App\Filament\Resources\Violations;

use BackedEnum;
use App\Models\Violation;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\Violations\Pages\EditViolation;
use App\Filament\Resources\Violations\Pages\ListViolations;
use App\Filament\Resources\Violations\Pages\CreateViolation;
use App\Filament\Resources\Violations\Schemas\ViolationForm;
use App\Filament\Resources\Violations\Tables\ViolationsTable;

class ViolationResource extends Resource
{
    protected static ?string $model = Violation::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::ExclamationTriangle;

    public static function form(Schema $schema): Schema
    {
        return ViolationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ViolationsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListViolations::route('/'),
            'create' => CreateViolation::route('/create'),
            'edit' => EditViolation::route('/{record}/edit'),
        ];
    }

public static function getEloquentQuery(): Builder
{
    $query = parent::getEloquentQuery();
    $user = auth()->user();

    // Jika siswa login, tampilkan hanya pelanggaran miliknya yang sudah disetujui
    if (auth()->user()->hasRole('siswa')) {
        $query->whereHas('student', function ($q) {
            $q->where('user_id', auth()->id());
        })
        ->where('status', 'approved');
    }

    // Jika orang tua login, tampilkan hanya pelanggaran anaknya yang disetujui
    if (auth()->user()->hasRole('ortu')) {
        $query->whereHas('student', function ($q) {
            $q->where('parent_user_id', auth()->id());
        })
        ->where('status', 'approved');
    }

    if (auth()->user()->hasRole('siswa')) {
        $query->whereHas('student', fn($q) =>
            $q->where('user_id', auth()->id())
        );
    }

    if (auth()->user()->hasRole('ortu')) {
        $query->whereHas('student', fn($q) =>
            $q->where('parent_user_id', auth()->id())
        );
    }

    if ($user->hasRole('guru')) {
        // Guru hanya bisa lihat laporan dia sendiri & semua yang approved
        $query->where(function ($q) use ($user) {
            $q->where('created_by', $user->id)
              ->orWhere('status', 'approved');
        });
    }

    if ($user->hasRole('siswa')) {
        // Siswa lihat pelanggaran dirinya sendiri
        $query->whereHas('student', fn ($q) => $q->where('user_id', $user->id));
    }

    if ($user->hasRole('ortu')) {
        // Ortu lihat pelanggaran anaknya
        $query->whereHas('student', fn ($q) => $q->where('parent_user_id', $user->id));
    }

    return $query;
}

public static function canViewAny(): bool
{
    return auth()->user()->hasAnyRole(['admin', 'guru', 'siswa', 'ortu']);
}

public static function canCreate(): bool
{
    return auth()->user()->hasAnyRole(['admin', 'guru']);
}

public static function canEdit($record): bool
{
     $user = auth()->user();

    // Admin boleh edit semua
    if ($user->hasRole('admin')) {
        return true;
    }

    // Guru boleh edit pelanggaran yang dia buat sendiri
    if ($user->hasRole('guru') && $record->created_by === $user->id) {
        return true;
    }

    return false;
}

public static function canDelete($record): bool
{
    $user = auth()->user();

    // Admin boleh edit semua
    if ($user->hasRole('admin')) {
        return true;
    }

    // Guru boleh edit pelanggaran yang dia buat sendiri
    if ($user->hasRole('guru') && $record->created_by === $user->id) {
        return true;
    }

    return false;
}

protected function mutateFormDataBeforeCreate(array $data): array
{
    // Jika user yang login adalah guru, set dia sebagai pembuat laporan
    if (auth()->check() && auth()->user()->hasRole('guru')) {
        $data['created_by'] = $data['created_by'] ?? auth()->id();
    }

    return $data;
}


}
