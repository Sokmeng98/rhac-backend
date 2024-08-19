<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Post_Category;

class Post_Subcategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'name_en',
        'post__categories_id',
        'slug_kh',
        'slug_en',
        'post_count'
    ];

    protected $dates = [
        'created_at',
        'updated_at'
    ];

    public function post__categories()
    {
        return $this->belongsTo(Post_Category::class);
    }

    public function posts()
    {
        return $this->belongsToMany(Post::class, 'post__post_category');
    }

    public function videos() {
        return $this->belongsToMany(Video::class, 'video__post_subcategory');
    }
}