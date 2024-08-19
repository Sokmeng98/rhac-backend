<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $adminRole = Role::create(['name' => 'admin']);
        $userRole = Role::create(['name' => 'user']);

        $editUsers = Permission::create(['name' => 'edit users']);
        $editPosts = Permission::create(['name' => 'edit posts']);

        $adminRole->givePermissionTo($editUsers);
        $userRole->givePermissionTo($editPosts);
    }
}
