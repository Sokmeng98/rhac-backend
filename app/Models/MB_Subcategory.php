<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MB_Subcategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'img',
        'name',
        'name_en',
        'slug_kh',
        'slug_en',
        'count',
        'm_b__categories_id'
    ];

    protected $dates = [
        'created_at',
        'updated_at'
    ];

    public function m_b__categories()
    {
        return $this->belongsTo(MB_Category::class);
    }

    public function m_b__learners()
    {
        return $this->belongsToMany(MB_Learner::class, 'm_b__learner_subcategory');
    }

    public function videos()
    {
        return $this->belongsToMany(Video::class, 'video__mb_subcategory');
    }
}
