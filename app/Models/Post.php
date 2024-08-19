<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Post_Subcategory;
use App\Models\Post_Category;
use App\Models\User;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'image',
        'title_kh',
        'title_en',
        'content_kh',
        'content_en',
        'excerpt_kh',
        'excerpt_en',
        'pdf',
        'date',
        'modified',
        'tags',
        'post__subcategories_id',
        'post__categories_id',
        'users_id',
        'view',
        'slug_kh',
        'slug_en',
        'status',
        'author'
    ];

    protected $dates = [
        'created_at',
        'updated_at'
    ];

    public function post__subcategories()
    {
        return $this->belongsToMany(Post_Subcategory::class, 'post__post_subcategory');
    }

    public function post__categories()
    {
        return $this->belongsToMany(Post_Category::class, 'post__post_category');
    }

    public function users()
    {
        return $this->belongsTo(User::class);
    }
}
