<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum InvoiceStatusEnum: string implements HasLabel
{
    case Open = 'Open';
    case Approved = 'Approved';
    case Sent = 'Sent';
    case Canceled = 'Canceled';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Open => __('Open'),
            self::Approved => __('Approved'),
            self::Sent => __('Sent'),
            self::Canceled => __('Canceled')
        };
    }
}
