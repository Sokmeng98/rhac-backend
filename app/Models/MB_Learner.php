<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\MB_Category;

class MB_Learner extends Model
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

    protected $casts = [
        'pdf' => 'array',
    ];

    public function m_b__categories()
    {
        return $this->belongsToMany(MB_Category::class, 'm_b__learner_category');
    }

    public function m_b__subcategories()
    {
        return $this->belongsToMany(MB_Subcategory::class, 'm_b__learner_subcategory');
    }

    public function users()
    {
        return $this->belongsTo(User::class);
    }
}
