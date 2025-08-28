<?php

namespace App\Filament\Resources\InvoiceResource\Pages;

use App\Filament\Resources\InvoiceResource;
use App\Models\Room;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateInvoice extends CreateRecord
{
    protected static string $resource = InvoiceResource::class;


    protected function handleRecordCreation(array $data): Model
    {
        $record =static::getModel()::create($data);
        Room::find($record->room_id)->update([
            'latest_meter_reading' => $record->new_reading,
        ]);

        return $record;
    }
}
