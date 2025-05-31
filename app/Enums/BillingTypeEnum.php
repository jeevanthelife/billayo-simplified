<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum BillingTypeEnum: string implements HasLabel
{
    case Monthly = 'Monthly';
    case Trimonthly = 'Trimonthly';
    case Custom = 'Custom';
    case Maintenance = 'Maintenance';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Monthly => __('Monthly'),
            self::Trimonthly => __('Trimonthly'),
            self::Custom => __('Custom'),
            self::Maintenance => __('Maintenance')
        };
    }
}
