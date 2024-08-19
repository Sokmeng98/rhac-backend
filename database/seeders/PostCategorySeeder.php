<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Post_Category;

class PostCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Post_Category::create([
            'id' => 1,
            'name' => 'ព័ត៌មានសុខភាពយុវវ័យ',
            'name_en' => 'YOUTH HEALTH NEWS',
            'slug_kh' => '%e1%9e%96%e1%9f%90%e1%9e%8f%e1%9f%8c%e1%9e%98%e1%9e%b6%e1%9e%93%e1%9e%9f%e1%9e%bb%e1%9e%81%e1%9e%97%e1%9e%b6%e1%9e%96%e1%9e%99%e1%9e%bb%e1%9e%9c%e1%9e%9c%e1%9f%90%e1%9e%99',
            'slug_en' => 'youth-health-news',
            'post_count' => 0
        ]);

        Post_Category::create([
            'id' => 2,
            'name' => 'រឿងជោគជ័យ',
            'name_en' => 'SUCCESS STORY',
            'slug_kh' => '%e1%9e%9a%e1%9e%bf%e1%9e%84%e1%9e%87%e1%9f%84%e1%9e%82%e1%9e%87%e1%9f%90%e1%9e%99',
            'slug_en' => 'success-story',
            'post_count' => 0
        ]);

        Post_Category::create([
            'id' => 3,
            'name' => 'ការបណ្តុះបណ្តាល',
            'name_en' => 'TRAINING',
            'slug_kh' => '%e1%9e%80%e1%9e%b6%e1%9e%9a%e1%9e%94%e1%9e%8e%e1%9f%92%e1%9e%8f%e1%9e%bb%e1%9f%87%e1%9e%94%e1%9e%8e%e1%9f%92%e1%9e%8f%e1%9e%b6%e1%9e%9b',
            'slug_en' => 'training',
            'post_count' => 0
        ]);

        Post_Category::create([
            'id' => 4,
            'name' => 'ការបោះពុម្ពផ្សាយ',
            'name_en' => 'PUBLICATION',
            'slug_kh' => '%e1%9e%80%e1%9e%b6%e1%9e%9a%e1%9e%94%e1%9f%84%e1%9f%87%e1%9e%96%e1%9e%bb%e1%9e%98%e1%9f%92%e1%9e%96%e1%9e%95%e1%9f%92%e1%9e%9f%e1%9e%b6%e1%9e%99',
            'slug_en' => 'publication',
            'post_count' => 0
        ]);

        Post_Category::create([
            'id' => 5,
            'name' => 'ព័ត៌មាន',
            'name_en' => 'NEWS',
            'slug_kh' => '%e1%9e%96%e1%9f%90%e1%9e%8f%e1%9f%8c%e1%9e%98%e1%9e%b6%e1%9e%93',
            'slug_en' => 'news',
            'post_count' => 0
        ]);

        Post_Category::create([
            'id' => 6,
            'name' => 'អាជីព និងឱកាស',
            'name_en' => 'CAREER AND OPPORTUNITY',
            'slug_kh' => '%e1%9e%80%e1%9e%b6%e1%9e%9a%e1%9e%84%e1%9e%b6%e1%9e%9a-%e1%9e%93%e1%9e%b7%e1%9e%84%e1%9e%a2%e1%9e%b6%e1%9e%87%e1%9e%b8%e1%9e%96',
            'slug_en' => 'career-and-opportunity',
            'post_count' => 0
        ]);
    }
}
