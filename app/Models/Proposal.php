<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Proposal extends Model
{
    use HasFactory;

    public $fillable = [
        'project_id', 'lecture_id', 'status', 'note'
    ];

    public function getDetail() {
        $proposal = Proposal::find($this->id);
        $members = [];
        $attachments = [];
        foreach (ProposalMember::where('proposal_id', $this->id)->get() as $member) {
            $members[] = User::find($member->id)->getDetail();
        }
        $proposal->members = $members;
        foreach (ProposalAttachment::where('proposal_id', $this->id)->get() as $attachment) {
            $attachment->filepath = url('uploads/'.$attachment->filepath);
            $attachments[] = $attachment;
        }
        $proposal->attachments = $attachment;
        return $proposal;
    }
}
