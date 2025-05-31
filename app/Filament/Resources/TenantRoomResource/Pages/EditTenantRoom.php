<?php

namespace App\Filament\Resources\TenantRoomResource\Pages;

use App\Filament\Resources\TenantRoomResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTenantRoom extends EditRecord
{
    protected static string $resource = TenantRoomResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->label(__("Remove"))
                ->tooltip(__("Remove Tenant from Room")),
        ];
    }
}
