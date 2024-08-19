<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contact_Us extends Model
{
    use HasFactory;

    protected $fillable = [
        'email',
        'phone',
        'name',
        'subject',
        'message'
    ];
    
    protected $dates = [
        'created_at',
        'updated_at'
    ];
}
