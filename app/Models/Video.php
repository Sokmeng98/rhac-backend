<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Video extends Model
{
    use HasFactory;

    protected $fillable = [
        'title_kh',
        'title_en',
        'video_url',
        'mb_professional',
        'date',
        'modified',
    ];

    protected $dates = [
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'mb_professional' => 'array',
    ];

    public function post__categories()
    {
        return $this->belongsToMany(Post_Category::class, 'video__post_category');
    }

    public function post__subcategories()
    {
        return $this->belongsToMany(Post_Subcategory::class, 'video__post_subcategory');
    }

    public function m_b__categories()
    {
        return $this->belongsToMany(MB_Category::class, 'video__mb_category');
    }

    public function m_b__subcategories()
    {
        return $this->belongsToMany(MB_Subcategory::class, 'video__mb_subcategory');
    }
}
