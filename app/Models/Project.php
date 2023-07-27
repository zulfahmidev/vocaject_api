<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Project extends Model
{
    use HasFactory;

    public $fillable = [
        'company_id', 'title', 'expired_at', 'deadline_at', 'description', 'budget', 'address', 'phone', 'category_id'
    ];

    public function getDetail() {
        $project = Project::find($this->id);
        $project->company = User::find($this->company_id)->getDetail();
        $project->expired_at = explode(' ', $project->expired_at)[0];
        $project->deadline_at = explode(' ', $project->deadline_at)[0];
        $project->category = ProjectCategory::find($this->category_id);
        $project->status = $this->getStatus();
        unset($project->company_id);
        unset($project->category_id);
        return $project;
    }

    public function getStatus() {
        $proposal = Proposal::where('project_id', $this->id)->where('status', 'accepted')->first();
        $status = 'opened';
        if ($proposal) {
            $status = 'closed';
        }
        if ($this->expired_at < now()) {
            $status = 'close';
        }
        $checkeds = ProjectTask::where('project_id', $this->id)->pluck('checked');
        if (!in_array(false, $checkeds->toArray())) {
            $status = 'completed';
        }
        return $status;
    }

    public function getAccProposal() {
        return Proposal::where('project_id', $this->id)->where('status', 'accepted')->first();
    }
}
