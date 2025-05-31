<?php

namespace App\Filament\Resources\TenantRoomResource\Pages;

use App\Filament\Resources\TenantRoomResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTenantRooms extends ListRecords
{
    protected static string $resource = TenantRoomResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
