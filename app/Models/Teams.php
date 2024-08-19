<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Teams_Category;

class Teams extends Model
{
    use HasFactory;

    protected $fillable = [
        'name_en',
        'name_kh',
        'img',
        'role_en',
        'role_kh',
        'order',
        'type',
    ];
    
    protected $dates = [
        'created_at',
        'updated_at'
    ];

    public function teams__category()
    {
        return $this->belongsTo(Teams_Category::class);
    }
}
