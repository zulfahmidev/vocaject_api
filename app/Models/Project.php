<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    public $fillable = [
        'company_id', 'title', 'description', 'address', 'phone', 'category_id'
    ];
}
