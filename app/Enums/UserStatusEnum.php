<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum UserStatusEnum: string implements HasLabel
{
    case Active = 'Active';
    case Inactive = 'Inactive';
    case Deleted = 'Deleted';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Active => __('Active'),
            self::Inactive => __('Inactive'),
            self::Deleted => __('Deleted')
        };
    }
}
