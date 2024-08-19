<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MB_Professional extends Model
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
        'grade',
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
        'grade' => 'array'
    ];

    public function users()
    {
        return $this->belongsTo(User::class);
    }
}