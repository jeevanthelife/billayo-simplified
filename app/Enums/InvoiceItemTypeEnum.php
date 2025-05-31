<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum InvoiceItemTypeEnum: string implements HasLabel
{
    case Electricity = 'Electricity';
    case Water = 'Water';
    case Waste = 'Waste';
    case Internet = 'Internet';
    case Others = 'Others';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Electricity => __('Electricity'),
            self::Water => __('Water'),
            self::Waste => __('Waste'),
            self::Internet => __('Internet'),
            self::Others => __('Others')
        };
    }
}
