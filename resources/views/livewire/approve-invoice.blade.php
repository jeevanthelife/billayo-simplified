<div>
    {{-- To attain knowledge, add things every day; To attain wisdom, subtract things every day. --}}
    <div class="flex gap-3 pt-4">
        <x-filament::button color="gray" icon="heroicon-o-printer" wire:click="printInvoice">
            Print PDF
        </x-filament::button>

        {{-- Approve Modal --}}
        @if (!$this->approved)
            <x-filament::modal id="approveBillModal" icon="heroicon-o-check-circle">
                <x-slot name="trigger">
                    <x-filament::button color="success" icon="heroicon-o-check-circle">
                        Approve Bill
                    </x-filament::button>
                </x-slot>

                <x-slot name="heading">Approve Bill</x-slot>
                <x-slot name="description">Add Payment Methods before approving the bill</x-slot>

                <form wire:submit="approveInvoice" class="space-y-6">
                    {{ $this->form }}

                    <div class="text-right">
                        <x-filament::button type="submit" color="success" icon="heroicon-o-check-circle">
                            {{ __('Approve') }}
                        </x-filament::button>
                    </div>
                </form>
            </x-filament::modal>
        @endif
    </div>
</div>
