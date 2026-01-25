<?php

namespace App\Filament\Resources\InvoiceResource\Pages;

use App\Enums\InvoiceStatusEnum;
use App\Filament\Resources\InvoiceResource;
use App\Models\PaymentMethod;
use Filament\Actions;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewInvoice extends ViewRecord
{
    protected static string $resource = InvoiceResource::class;
    protected static string $view = 'filament.resources.invoice-resource.pages.view-invoice';

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->visible(
                    fn($record) =>
                    in_array($record->status, [
                        InvoiceStatusEnum::Open->value,
                        InvoiceStatusEnum::Canceled->value,
                    ])
                ),
        ];
    }

    public function mount(int | string $record): void
    {
        $this->record = $this->resolveRecord($record);
        $this->record->paymentOptions = $this->record->invoicePaymentOptions->map(function ($option) {
            $media = $option->getFirstMedia();
            return [
                'url' => $media->getUrl(),
                'full_url' => $media->getFullUrl(),
                'path' => $media->getPath(),
                'file_name' => $media->name,
                'payment_name' => $option->name,
                'payment_type' => $option->type,
            ];
        });

        $this->authorizeAccess();

        if (! $this->hasInfolist()) {
            $this->fillForm();
        }
    }
}
