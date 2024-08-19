<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Post_Subcategory;

class PostSubCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Post_Subcategory::create([
            'id' => 1,
            'name' => 'បម្រែបម្រួលជីវសាស្រ្ត និងការបន្តពូជ',
            'name_en' => 'BIOLOGICAL CHANGES &REPRODUCTION',
            'slug_kh' => '%e1%9e%94%e1%9e%98%e1%9f%92%e1%9e%9a%e1%9f%82%e1%9e%94%e1%9e%98%e1%9f%92%e1%9e%9a%e1%9e%bd%e1%9e%9b%e1%9e%87%e1%9e%b8%e1%9e%9c%e1%9e%9f%e1%9e%b6%e1%9e%9f%e1%9f%92%e1%9e%9a%e1%9f%92%e1%9e%8f-%e1%9e%93',
            'slug_en' => 'biological-changes-reproduction-en',
            'post__categories_id' => 1,
            'post_count' => 0
        ]);

        Post_Subcategory::create([
            'id' => 2,
            'name' => 'សុខភាពផ្លូវភេទ និងសុខភាពបន្តពូជ',
            'name_en' => 'SEXUAL REPRODUCTIVE HEALTH',
            'slug_kh' => '%e1%9e%9f%e1%9e%bb%e1%9e%81%e1%9e%97%e1%9e%b6%e1%9e%96%e1%9e%95%e1%9f%92%e1%9e%9b%e1%9e%bc%e1%9e%9c%e1%9e%97%e1%9f%81%e1%9e%91-%e1%9e%93%e1%9e%b7%e1%9e%84%e1%9e%9f%e1%9e%bb%e1%9e%81%e1%9e%97%e1%9e%b6',
            'slug_en' => 'sexual-reproductive-health-en',
            'post__categories_id' => 1,
            'post_count' => 0
        ]);

        Post_Subcategory::create([
            'id' => 3,
            'name' => 'យល់ដឹងពីផ្លូវភេទនិង ភាពពេញវ័យគ្រប់ការ',
            'name_en' => 'GET KNOWING ABOUT SEXUALITY',
            'slug_kh' => '%e1%9e%99%e1%9e%9b%e1%9f%8b%e1%9e%8a%e1%9e%b9%e1%9e%84%e1%9e%96%e1%9e%b8%e1%9e%95%e1%9f%92%e1%9e%9b%e1%9e%bc%e1%9e%9c%e1%9e%97%e1%9f%81%e1%9e%91%e1%9e%93%e1%9e%b7%e1%9e%84-%e1%9e%97%e1%9e%b6%e1%9e%96',
            'slug_en' => 'get-knowing-about-sexuality-en',
            'post__categories_id' => 1,
            'post_count' => 0
        ]);

        Post_Subcategory::create([
            'id' => 4,
            'name' => 'ការប្រាស្រ័យទាក់ទង និងការសម្រេចចិត្ត',
            'name_en' => 'INTERPERSONAL RELATIONSHIP AND DECISION MAKING',
            'slug_kh' => '%e1%9e%80%e1%9e%b6%e1%9e%9a%e1%9e%94%e1%9f%92%e1%9e%9a%e1%9e%b6%e1%9e%9f%e1%9f%92%e1%9e%9a%e1%9f%90%e1%9e%99%e1%9e%91%e1%9e%b6%e1%9e%80%e1%9f%8b%e1%9e%91%e1%9e%84-%e1%9e%93%e1%9e%b7%e1%9e%84%e1%9e%80',
            'slug_en' => 'interpersonal-and-decision-making',
            'post__categories_id' => 1,
            'post_count' => 0
        ]);

        Post_Subcategory::create([
            'id' => 5,
            'name' => 'កាមរោគ អេដស៍ និងគ្រឿងញៀន',
            'name_en' => 'STIS, HIV/AIDS &ADDICTED SUBSTANCES',
            'slug_kh' => '%e1%9e%80%e1%9e%b6%e1%9e%98%e1%9e%9a%e1%9f%84%e1%9e%82-%e1%9e%a2%e1%9f%81%e1%9e%8a%e1%9e%9f%e1%9f%8d-%e1%9e%93%e1%9e%b7%e1%9e%84%e1%9e%82%e1%9f%92%e1%9e%9a%e1%9e%bf%e1%9e%84%e1%9e%89%e1%9f%80%e1%9e%93',
            'slug_en' => 'stis-hivaids-addicted-substances',
            'post__categories_id' => 1,
            'post_count' => 0
        ]);

        Post_Subcategory::create([
            'id' => 6,
            'name' => 'ភេទ យេនឌ័រ និងអំពើហឹង្សា',
            'name_en' => 'SEX, GENDER &VIOLENCE',
            'slug_kh' => '%e1%9e%97%e1%9f%81%e1%9e%91-%e1%9e%99%e1%9f%81%e1%9e%93%e1%9e%8c%e1%9f%90%e1%9e%9a-%e1%9e%93%e1%9e%b7%e1%9e%84%e1%9e%a2%e1%9f%86%e1%9e%96%e1%9e%be%e1%9e%a0%e1%9e%b9%e1%9e%84%e1%9f%92%e1%9e%9f%e1%9e%b6',
            'slug_en' => 'gender-violence',
            'post__categories_id' => 1,
            'post_count' => 0
        ]);

        Post_Subcategory::create([
            'id' => 7,
            'name' => 'ផ្អែកលើគម្រោង',
            'name_en' => 'Project based',
            'slug_kh' => '%e1%9e%95%e1%9f%92%e1%9e%a2%e1%9f%82%e1%9e%80%e1%9e%9b%e1%9e%be%e1%9e%82%e1%9e%98%e1%9f%92%e1%9e%9a%e1%9f%84%e1%9e%84',
            'slug_en' => 'project-based',
            'post__categories_id' => 6,
            'post_count' => 0
        ]);

        Post_Subcategory::create([
            'id' => 8,
            'name' => 'ពេញ​ម៉ោង',
            'name_en' => 'Full Time',
            'slug_kh' => '%e1%9e%96%e1%9f%81%e1%9e%89%e2%80%8b%e1%9e%98%e1%9f%89%e1%9f%84%e1%9e%84',
            'slug_en' => 'full-time',
            'post__categories_id' => 6,
            'post_count' => 0
        ]);

        Post_Subcategory::create([
            'id' => 9,
            'name' => 'ឯករាជ្យ',
            'name_en' => 'Freelance',
            'slug_kh' => '%e1%9e%af%e1%9e%80%e1%9e%9a%e1%9e%b6%e1%9e%87%e1%9f%92%e1%9e%99',
            'slug_en' => 'freelance',
            'post__categories_id' => 6,
            'post_count' => 0
        ]);

        Post_Subcategory::create([
            'id' => 10,
            'name' => 'ក្រៅ​ម៉ោង',
            'name_en' => 'Part Time',
            'slug_kh' => '%e1%9e%80%e1%9f%92%e1%9e%9a%e1%9f%85%e2%80%8b%e1%9e%98%e1%9f%89%e1%9f%84%e1%9e%84',
            'slug_en' => 'part-time',
            'post__categories_id' => 6,
            'post_count' => 0
        ]);
    }
}