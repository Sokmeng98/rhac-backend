<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\MB_Main_Category;

class MB_Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'img',
        'name',
        'name_en',
        'slug_kh',
        'slug_en',
        'count'
    ];

    protected $dates = [
        'created_at',
        'updated_at'
    ];

    public function m_b__subcategories()
    {
        return $this->hasMany(MB_Subcategory::class, 'm_b__categories_id');
    }
    public function m_b__learners()
    {
        return $this->belongsToMany(MB_Learner::class, 'm_b__learner_category');
    }

    public function videos()
    {
        return $this->belongsToMany(Video::class, 'video_mb_category');
    }
}
