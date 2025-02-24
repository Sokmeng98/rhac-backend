<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'address',
        'content',
        'coordinate',
        'phone',
        'url'
    ];
    
    protected $dates = [
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'coordinate' => 'array',
        'phone' => 'array'
    ];
}
