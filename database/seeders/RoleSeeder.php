<?php

namespace Database\Seeders;

use App\Enums\RoleEnum;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = RoleEnum::getAllValues();
        Permission::create(['name' => 'view_role']);
        Permission::create(['name' => 'view_any_role']);
        Permission::create(['name' => 'create_role']);
        Permission::create(['name' => 'update_role']);
        Permission::create(['name' => 'delete_role']);
        Permission::create(['name' => 'delete_any_role']);
        Permission::create(['name' => 'view_shield::role']);
        Permission::create(['name' => 'view_any_shield::role']);
        Permission::create(['name' => 'create_shield::role']);
        Permission::create(['name' => 'update_shield::role']);
        Permission::create(['name' => 'delete_any_shield::role']);
        Permission::create(['name' => 'delete_shield::role']);

        foreach ($roles as $role) {
            $createdRole = Role::create([
                "name" => $role,
                "guard_name" =>  "web",
            ]);

            if ($createdRole->name == "super_admin") {
                $createdRole->givePermissionTo(Permission::all());
            }
        }
    }
}
