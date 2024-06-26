<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProposalMember extends Model
{
    use HasFactory;

    public $fillable = [
        'proposal_id', 'student_id'
    ];
}
