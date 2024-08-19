<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FAQ extends Model
{
    use HasFactory;
    protected $fillable = [
        'question_kh',
        'question_en',
        'answer_kh',
        'answer_en',
        'type'
    ];
    
    protected $dates = [
        'created_at',
        'updated_at'
    ];
}
