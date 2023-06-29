<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentDetail extends Model
{
    use HasFactory;

    public $fillable = [
        'user_id', 'nim', 'address', 'phone', 'college_id'
    ];
}
