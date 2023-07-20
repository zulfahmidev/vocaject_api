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
        $proposal->project = Project::find($this->project_id)->getDetail();
        $proposal->lecture = User::find($this->lecture_id)->getDetail();
        unset($proposal->project_id);
        unset($proposal->lecture_id);
        $members = [];
        $attachments = [];
        foreach (ProposalMember::where('proposal_id', $this->id)->get() as $member) {
            $member = User::find($member->student_id);
            if ($member) {
                $members[] = $member->getDetail();
            }
        }
        $proposal->members = $members;
        foreach (ProposalAttachment::where('proposal_id', $this->id)->get() as $attachment) {
            $attachment->filepath = secure_url('uploads/'.$attachment->filepath);
            $attachments[] = $attachment;
        }
        $proposal->attachments = $attachments;
        return $proposal;
    }
}
