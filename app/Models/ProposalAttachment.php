<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProposalAttachment extends Model
{
    use HasFactory;

    public $fillable = [
        'filepath', 'proposal_id', 'filename', 'mimetype'
    ];
}
