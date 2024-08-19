<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'name_en',
        'name_kh',
        'img',
    ];

    protected $dates = [
        'created_at',
        'updated_at'
    ];
}