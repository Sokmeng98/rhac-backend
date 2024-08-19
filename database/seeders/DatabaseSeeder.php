<?php

namespace Database\Seeders;

use App\Models\MB_Subcategory;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory(10)->create();
        $this->call(RolePermissionSeeder::class);
        $this->call(UserSeeder::class);
        $this->call(PostCategorySeeder::class);
        $this->call(PostSubCategorySeeder::class);
        $this->call(MB_CategorySeeder::class);
        $this->call(MB_SubcategorySeeder::class);
    }
}
