<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProposalAttachment extends Model
{
    use HasFactory;

    public $fillable = [
        'document_id', 'proposal_id'
    ];

    public function getDetail() {
        $data = Document::find($this->document_id)->getData();
        $data->id = $this->id;
        $data->proposal_id = $this->proposal_id;
        return $data;
    }
}
