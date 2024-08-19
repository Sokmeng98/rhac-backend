<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MB_Professional_Learning extends Model
{
    use HasFactory;

    protected $fillable = [
        'title_kh',
        'title_en',
        'type',
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
