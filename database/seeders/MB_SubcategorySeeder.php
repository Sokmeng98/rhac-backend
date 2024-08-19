<?php

namespace Database\Seeders;

use App\Models\MB_Subcategory;
use Illuminate\Database\Seeder;

class MB_SubcategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        MB_Subcategory::create([
            'id' => 1,
            'name' => 'រាងកាយខ្ញុំ និងការវិវដ្ត៏នៃរាងកាយ',
            'name_en' => 'Body Evolution',
            'slug_kh' => '%E1%9E%9A%E1%9E%B6%E1%9E%84%E1%9E%80%E1%9E%B6%E1%9E%99%E1%9E%81%E1%9F%92%E1%9E%89%E1%9E%BB%E1%9F%86%20%E1%9E%93%E1%9E%B7%E1%9E%84%E1%9E%80%E1%9E%B6%E1%9E%9A%E1%9E%9C%E1%9E%B7%E1%9E%9C%E1%9E%8A%E1%9F%92%E1%9E%8F%E1%9F%8F%E1%9E%93%E1%9F%83%E1%9E%9A%E1%9E%B6%E1%9E%84%E1%9E%80%E1%9E%B6%E1%9E%99',
            'slug_en' => ' body-evolution',
            'm_b__categories_id' => 1,
            'count' => 0
        ]);
        MB_Subcategory::create([
            'id' => 2,
            'name' => 'ទំនាក់ទំនង',
            'name_en' => 'Communication',
            'slug_kh' => '%E1%9E%91%E1%9F%86%E1%9E%93%E1%9E%B6%E1%9E%80%E1%9F%8B%E1%9E%91%E1%9F%86%E1%9E%93%E1%9E%84',
            'slug_en' => 'communication',
            'm_b__categories_id' => 2,
            'count' => 0
        ]);
        MB_Subcategory::create([
            'id' => 3,
            'name' => 'រឿងភេទ និងអក្បកិរយាផ្លូវភេទ',
            'name_en' => 'Sex and Sexual Activity',
            'slug_kh' => '%E1%9E%9A%E1%9E%BF%E1%9E%84%E1%9E%97%E1%9F%81%E1%9E%91%20%E1%9E%93%E1%9E%B7%E1%9E%84%E1%9E%A2%E1%9E%80%E1%9F%92%E1%9E%94%E1%9E%80%E1%9E%B7%E1%9E%9A%E1%9E%99%E1%9E%B6%E1%9E%95%E1%9F%92%E1%9E%9B%E1%9E%BC%E1%9E%9C%E1%9E%97%E1%9F%81%E1%9E%91',
            'slug_en' => 'sex-and-sexual-activity',
            'm_b__categories_id' => 2,
            'count' => 0
        ]);
        MB_Subcategory::create([
            'id' => 4,
            'name' => 'តម្លៃ សិទ្ធ​ និងរឿងផ្លូវភេទ',
            'name_en' => 'Values, Rights and Sex Matters',
            'slug_kh' => '%E1%9E%8F%E1%9E%98%E1%9F%92%E1%9E%9B%E1%9F%83%20%E1%9E%9F%E1%9E%B7%E1%9E%91%E1%9F%92%E1%9E%92%E2%80%8B%20%E1%9E%93%E1%9E%B7%E1%9E%84%E1%9E%9A%E1%9E%BF%E1%9E%84%E1%9E%95%E1%9F%92%E1%9E%9B%E1%9E%BC%E1%9E%9C%E1%9E%97%E1%9F%81%E1%9E%91',
            'slug_en' => 'values,-rights-and-sex-matters',
            'm_b__categories_id' => 2,
            'count' => 0
        ]);
        MB_Subcategory::create([
            'id' => 5,
            'name' => 'សុខភាពផ្លូវភេទ',
            'name_en' => 'Sexual Health',
            'slug_kh' => '%E1%9E%9F%E1%9E%BB%E1%9E%81%E1%9E%97%E1%9E%B6%E1%9E%96%E1%9E%95%E1%9F%92%E1%9E%9B%E1%9E%BC%E1%9E%9C%E1%9E%97%E1%9F%81%E1%9E%91',
            'slug_en' => 'sexual-health',
            'm_b__categories_id' => 3,
            'count' => 0
        ]);
        MB_Subcategory::create([
            'id' => 6,
            'name' => 'ថែរក្សាសុវត្ថិភាព',
            'name_en' => 'Maintain Security',
            'slug_kh' => '%E1%9E%90%E1%9F%82%E1%9E%9A%E1%9E%80%E1%9F%92%E1%9E%9F%E1%9E%B6%E1%9E%9F%E1%9E%BB%E1%9E%9C%E1%9E%8F%E1%9F%92%E1%9E%90%E1%9E%B7%E1%9E%97%E1%9E%B6%E1%9E%96',
            'slug_en' => 'maintain-security',
            'm_b__categories_id' => 4,
            'count' => 0
        ]);
        MB_Subcategory::create([
            'id' => 7,
            'name' => 'ជំនាញថែរក្សាសុខភាព​ / សុខមាលភាព',
            'name_en' => 'Health Care Skills / Health',
            'slug_kh' => '%E1%9E%87%E1%9F%86%E1%9E%93%E1%9E%B6%E1%9E%89%E1%9E%90%E1%9F%82%E1%9E%9A%E1%9E%80%E1%9F%92%E1%9E%9F%E1%9E%B6%E1%9E%9F%E1%9E%BB%E1%9E%81%E1%9E%97%E1%9E%B6%E1%9E%96%E2%80%8B%20%2F%20%E1%9E%9F%E1%9E%BB%E1%9E%81%E1%9E%98%E1%9E%B6%E1%9E%9B%E1%9E%97%E1%9E%B6%E1%9E%96',
            'slug_en' => 'health-care-skills-health',
            'm_b__categories_id' => 4,
            'count' => 0
        ]);
    }
}
