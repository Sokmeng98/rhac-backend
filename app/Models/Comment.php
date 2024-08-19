<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_name',
        'content',
        'post_id',
        'mb_learner_id',
        'mb_professional_id',
        'checked'
    ];

    protected $dates = [
        'created_at',
        'updated_at'
    ];
}
