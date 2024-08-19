<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $adminRole = Role::where('name', 'admin')->get();
        $userRole = Role::where('name', 'user')->get();

        User::create([
            'name' => 'Admin',
            'email' => 'sangsonyrath17@kit.edu.kh',
            'password' => Hash::make('Admin@123'),
        ])->assignRole($adminRole);

        User::create([
            'name' => 'User',
            'email' => 'yim.sunleang19@kit.edu.kh',
            'password' => Hash::make('User@123'),
        ])->assignRole($userRole);
    }
}