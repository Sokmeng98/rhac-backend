<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Post_Subcategory;

class Post_Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'name_en',
        'slug_kh',
        'slug_en',
        'post_count'
    ];

    protected $dates = [
        'created_at',
        'updated_at'
    ];

    public function post__subcategories()
    {
        return $this->hasMany(Post_Subcategory::class, 'post__categories_id');
    }

    public function posts() {
        return $this->belongsToMany(Post::class, 'post__post_subcategory');
    }

    public function videos() {
        return $this->belongsToMany(Video::class, 'video__post_category');
    }
}