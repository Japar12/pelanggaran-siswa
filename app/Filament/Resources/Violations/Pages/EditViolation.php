<?php

namespace App\Filament\Resources\Violations\Pages;

use Filament\Actions\DeleteAction;
use App\Events\ViolationStatusUpdated;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use App\Listeners\SendViolationStatusNotification;
use App\Filament\Resources\Violations\ViolationResource;

class EditViolation extends EditRecord
{
    protected static string $resource = ViolationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

      protected function afterSave(): void
    {
            // Log kecil buat pastikan triggernya berjalan
            \Log::info("📢 Event ViolationStatusUpdated dikirim untuk ID {$this->record->id}");
        }
    }


