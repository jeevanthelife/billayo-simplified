<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum RoleEnum: string implements HasLabel
{
    case SUPER_ADMIN = "super_admin";
    case Admin = "admin";
    case House_owner = "house_owner";
    case Tenant_user = "tenant_user";

    public function getLabel(): ?string
    {
        return match ($this) {
            self::SUPER_ADMIN => 'Super Admin',
            self::Admin => 'Admin',
            self::House_owner => 'House Owner',
            self::Tenant_user => 'Tenant User'
        };
    }

    public static function getAllValues(): array
    {
        return array_column(self::cases(), "value");
    }
}
