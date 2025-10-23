<?php

namespace App\Filament\Resources\Violations\Pages;

use App\Filament\Resources\Violations\ViolationResource;
use Filament\Resources\Pages\CreateRecord;
use App\Events\ViolationCreated;

class CreateViolation extends CreateRecord
{
    protected static string $resource = ViolationResource::class;

    /**
     * Dipanggil setelah data pelanggaran berhasil disimpan.
     */
    protected function afterCreate(): void
    {
        // Panggil event agar listener notifikasi berjalan
        event(new ViolationCreated($this->record));

        \Log::info('📢 Event ViolationCreated dipicu dari CreateViolation untuk ID: ' . $this->record->id);
    }
}
