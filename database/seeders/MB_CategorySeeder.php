<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MB_Category;

class MB_CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        MB_Category::create([
            'id' => 1,
            'name' => 'រាងកាយ',
            'name_en' => 'Body',
            'slug_kh' => '%e1%9e%9a%e1%9e%b6%e1%9e%84%e1%9e%80%e1%9e%b6%e1%9e%99',
            'slug_en' => 'body',
            'count' => 0
        ]);
        MB_Category::create([
            'id' => 2,
            'name' => 'ភេទ',
            'name_en' => 'Sex',
            'slug_kh' => '%e1%9e%97%e1%9f%81%e1%9e%91',
            'slug_en' => 'sex',
            'count' => 0
        ]);
        MB_Category::create([
            'id' => 3,
            'name' => 'សុខភាព',
            'name_en' => 'Health',
            'slug_kh' => '%e1%9e%9f%e1%9e%bb%e1%9e%81%e1%9e%97%e1%9e%b6%e1%9e%96',
            'slug_en' => 'health',
            'count' => 0
        ]);
        MB_Category::create([
            'id' => 4,
            'name' => 'សុខុមាលភាព',
            'name_en' => 'Wellbeing',
            'slug_kh' => '%e1%9e%9f%e1%9e%bb%e1%9e%81%e1%9e%bb%e1%9e%98%e1%9e%b6%e1%9e%9b%e1%9e%97%e1%9e%b6%e1%9e%96',
            'slug_en' => 'wellbeing',
            'count' => 0
        ]);
    }
}