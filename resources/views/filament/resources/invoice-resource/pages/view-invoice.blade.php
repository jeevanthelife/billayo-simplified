<x-filament-panels::page>
    <div class="space-y-6">

        {{-- Header --}}
        <div class="text-center">
            <h2 class="text-xl font-semibold">Bill Details</h2>
            <span class="text-gray-600">{{ $record->invoice_number }}</span>
            <p class="text-gray-600">
                {{ $record->billing_type }} - Room {{ $record->room->room_number }}
                ({{ $record->tenant->name }})
            </p>
        </div>

        {{-- Status --}}
        <div class="flex items-center justify-between border-b pb-4">
            <span>{{ $record->invoice_date }}</span>
            <span class="px-3 py-1 text-sm rounded-md border">
                {{ ucfirst($record->status) }}
            </span>
        </div>

        {{-- Bill Breakdown --}}
        <div>
            <h3 class="font-semibold mb-2">Bill Breakdown</h3>
            <div class="space-y-2">
                @foreach ($record->invoiceItems as $item)
                    <div class="flex justify-between text-sm">
                        <span>
                            {{ $item->title }}
                            @if ($item->description)
                                ({{ $item->description }})
                            @endif
                        </span>
                        <span>Rs. {{ number_format($item->amount, 2) }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Totals --}}
        <div class="border-t pt-3">
            <div class="flex justify-between font-semibold text-lg">
                <span>Total Amount</span>
                <span>Rs. {{ number_format($record->grand_total, 2) }}</span>
            </div>
            <div class="flex justify-between text-sm text-gray-600 mt-1">
                <span>Start-End Date</span>
                <span>{{ \Carbon\Carbon::parse($record->start_date)->format('m/d/Y') }} -
                    {{ \Carbon\Carbon::parse($record->end_date)->format('m/d/Y') }}
                </span>
            </div>
        </div>
        {{-- Actions --}}
        @livewire('approve-invoice', ['invoice' => $this->record])
    </div>
</x-filament-panels::page>
