<?php

namespace App\Filament\Resources\Violations\Pages;

use App\Filament\Resources\Violations\ViolationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListViolations extends ListRecords
{
    protected static string $resource = ViolationResource::class;

    protected function getHeaderActions(): array
    {
         if (auth()->user()?->hasAnyRole(['admin', 'guru'])) {
        return [
            CreateAction::make(),
        ];
    }

    return [];
    }
}
