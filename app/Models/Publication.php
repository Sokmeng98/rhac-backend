<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Publication extends Model
{
    use HasFactory;

    protected $fillable = [
        'title_kh',
        'title_en',
        'image',
        'pdf',
        'date',
        'modified',
    ];

    protected $dates = [
        'created_at',
        'updated_at'
    ];
}
