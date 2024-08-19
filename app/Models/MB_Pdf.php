<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MB_Pdf extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'pdf',
    ];
    
    protected $dates = [
        'created_at',
        'updated_at'
    ];
}
