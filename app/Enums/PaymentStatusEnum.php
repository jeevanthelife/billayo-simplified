<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum PaymentStatusEnum: string implements HasLabel
{
    case Pending = 'Pending';
    case Paid = 'Paid';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Pending => __('Pending'),
            self::Paid => __('Paid')
        };
    }
}
