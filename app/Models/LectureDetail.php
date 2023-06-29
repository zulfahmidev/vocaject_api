<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LectureDetail extends Model
{
    use HasFactory;

    public $fillable = [
        'user_id', 'nidn', 'address', 'phone', 'college_id'
    ];
}
