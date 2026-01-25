<?php

namespace App\Filament\Resources\InvoiceResource\Pages;

use App\Enums\InvoiceStatusEnum;
use App\Filament\Resources\InvoiceResource;
use App\Models\Invoice;
use App\Models\Room;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditInvoice extends EditRecord
{
    protected static string $resource = InvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->visible(
                    fn($record) => in_array(
                        $record->status,
                        [InvoiceStatusEnum::Open->value, InvoiceStatusEnum::Canceled->value]
                    )
                ),
        ];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $record->update($data);
        Room::find($record->room_id)->update([
            'latest_meter_reading' => $record->new_reading,
        ]);

        return $record;
    }
}
