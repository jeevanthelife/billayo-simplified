<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum RoleEnum: string implements HasLabel
{
    case SUPER_ADMIN = "super_admin";
    case Admin = "admin";

    public function getLabel(): ?string
    {
        return match ($this) {
            self::SUPER_ADMIN => 'Super Admin',
            self::Admin => 'Admin',
        };
    }

    public static function getAllValues(): array
    {
        return array_column(self::cases(), "value");
    }
}
