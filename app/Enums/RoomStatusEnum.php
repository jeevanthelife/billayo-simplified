<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum RoomStatusEnum: string implements HasLabel
{
    case Available = 'Available';
    case Occupied = 'Occupied';
    case Under_maintenance = 'Under Maintenance';
    case Unavailable = 'Unavailable';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Available => __('Available'),
            self::Occupied => __('Occupied'),
            self::Under_maintenance => __('Under Maintenance'),
            self::Unavailable => __('Unavailable')
        };
    }
}
