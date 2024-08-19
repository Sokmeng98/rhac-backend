<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class What_we_do extends Model
{
    use HasFactory;

    protected $fillable = [
        'icon',
        'title_kh',
        'title_en',
        'subtitle_kh',
        'subtitle_en'
    ];
    
    protected $dates = [
        'created_at',
        'updated_at'
    ];
}
