<?php

namespace Database\Seeders;

use App\Enums\RoleEnum;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            "first_name" => "Super",
            "last_name" => "Super Admin",
            "email" => "super-admin@example.com",
            "password" => Hash::make("password"),
        ])->assignRole(RoleEnum::SUPER_ADMIN->value);
    }
}
