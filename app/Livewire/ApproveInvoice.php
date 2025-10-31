<?php

namespace App\Livewire;

use App\Enums\InvoiceStatusEnum;
use App\Models\Invoice;
use App\Models\PaymentMethod;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Livewire\Component;

class ApproveInvoice extends Component implements HasForms
{
    use InteractsWithForms;

    public Invoice $invoice;
    public ?array $data = [];
    public bool $approved = false;

    public function mount(Invoice $invoice): void
    {
        $this->invoice = $invoice;
        $this->approved = $invoice->status == InvoiceStatusEnum::Approved->value;

        // Pre-fill the form with already selected payment methods (if any)
        $this->form->fill([
            'payment_methods' => $this->invoice->invoicePaymentOptions()->pluck('id')->toArray(),
        ]);
    }

    public function render()
    {
        return view('livewire.approve-invoice');
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('payment_methods')
                    ->label('Payment Details')
                    ->multiple()
                    ->options(PaymentMethod::pluck('name', 'id'))
                    ->required()
                    ->maxItems(3),
            ])
            ->statePath('data');
    }

    public function approveInvoice(): void
    {
        $state = $this->form->getState();

        // Sync payment options
        $this->invoice->invoicePaymentOptions()->sync($state['payment_methods']);

        // Update invoice status
        $this->invoice->update([
            'status' => InvoiceStatusEnum::Approved->value,
        ]);

        $this->reset('data');

        Notification::make()
            ->title('Invoice Approved Successfully')
            ->success()
            ->send();

        $this->dispatch('close-modal', id: 'approveBillModal');
    }

    public function printInvoice()
    {
        $paymentOptions = $this->invoice->invoicePaymentOptions->map(function ($option) {
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

        $invoiceItems = $this->invoice->invoiceItems->map(function ($item) {
            return [
                'title' => $item->title,
                'rate' => $item->rate,
                'quantity' => $item->quantity,
                'amount' => $item->amount,
            ];
        })->toArray();

        $invoicePdf = (object) [
            'invoice_number' => $this->invoice->invoice_number,
            'date' => date('d F Y'),
            'room_number' => $this->invoice->room->room_number,
            'tenant_name' => $this->invoice->tenant->name,
            'status' => $this->invoice->status,
            'start_date' => $this->invoice->start_date,
            'end_date' => $this->invoice->end_date,
            'sub_total' => $this->invoice->sub_total,
            'due_amount' => $this->invoice->due_amount,
            'advance_amount' => $this->invoice->advance_amount,
            'grand_total' => $this->invoice->grand_total,
            'items' => $invoiceItems,
            'payment_options' => $paymentOptions,
        ];

        $pdf = Pdf::loadView('invoices.pdf', compact('invoicePdf'))->setOption([
                "isRemoteEnabled" => true,
            ]);

        return response()->streamDownload(
            fn() => print($pdf->output()),
            'invoice-' . str_replace(' ', '-', $invoicePdf->tenant_name) . '-' . $invoicePdf->invoice_number . '.pdf'
        );
    }
}
