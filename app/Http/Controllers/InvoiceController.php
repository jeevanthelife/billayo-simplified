<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoiceController
{
    public function printInvoice($invoiceId)
    {
        $invoice = Invoice::find($invoiceId);
        $paymentOptions = $invoice->invoicePaymentOptions->map(function ($option) {
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

        $invoiceItems = $invoice->invoiceItems->map(function ($item) {
            return [
                'title' => $item->title,
                'rate' => $item->rate,
                'quantity' => $item->quantity,
                'amount' => $item->amount,
            ];
        })->toArray();

        $invoicePdf = (object) [
            'invoice_number' => $invoice->invoice_number,
            'date' => date('d F Y'),
            'room_number' => $invoice->room->room_number,
            'tenant_name' => $invoice->tenant->name,
            'status' => $invoice->status,
            'month' => $invoice->month,
            'start_date' => $invoice->start_date,
            'end_date' => $invoice->end_date,
            'sub_total' => $invoice->sub_total,
            'due_amount' => $invoice->due_amount,
            'advance_amount' => $invoice->advance_amount,
            'grand_total' => $invoice->grand_total,
            'items' => $invoiceItems,
            'payment_options' => $paymentOptions,
            'new_reading' => $invoice->new_reading,
            'previous_reading' => $invoice->previous_reading,
            'remarks' => $invoice->remarks,
        ];

        $pdf = Pdf::loadView('invoices.pdf', compact('invoicePdf'))->setOption([
            "isRemoteEnabled" => true,
        ]);

        return response()->streamDownload(
            fn() => print($pdf->output()),
            str_replace(' ', '_', $invoicePdf->tenant_name) . '_' . $invoicePdf->month . "_Bills_" . $invoicePdf->invoice_number  .  '.pdf'
        );
    }
}
