<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum TenantStatusEnum: string implements HasLabel
{
    case Active = 'Active';
    case Inactive = 'Inactive';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Active => __('Active'),
            self::Inactive => __('Inactive')
        };
    }
}
