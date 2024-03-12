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

    public static $tax = 0.05; // 5%

    public function getDetail() {
        $project = Project::find($this->id);
        $project->company = User::find($this->company_id)->getDetail();
        $project->expired_at = explode(' ', $project->expired_at)[0];
        $project->deadline_at = explode(' ', $project->deadline_at)[0];
        $project->category = ProjectCategory::find($this->category_id);
        $project->status = $this->getStatus();
        $project->managed_budget = ProjectBudget::where('project_id', $this->id)->first();

        $tasks = ProjectTask::where('project_id', $this->id)->count();
        $checkedTasks = ProjectTask::where('project_id', $this->id)->where('checked', 1)->count();
        $progress = ($tasks != 0 && $checkedTasks != 0) ? (100/$tasks)*$checkedTasks : 0;

        $project->progress = (int) $progress;

        $proposal = $this->getAccProposal();
        if ($proposal) {
            $members = ProposalMember::where('proposal_id', $proposal->id)->pluck('student_id');
            $proposal->members = array_map(function($v) {
                return User::find($v)->getDetail();
            }, $members->toArray());
        }

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
            $status = 'closed';
        }
        // $checkeds = ProjectTask::where('project_id', $this->id)->pluck('checked');
        if ($this->deadline_at < now()) {
            $status = 'completed';
        }
        return $status;
    }

    public function getAccProposal() {
        return Proposal::where('project_id', $this->id)->where('status', 'accepted')->first();
    }

    public function transferBudget() {
        $company = User::find($this->company_id);
        $total = (int)$this->budget + ((int)$this->budget * Project::$tax);
        if (!($company->balance >= $total)) {
            return false;
        }
        $company->balance = $company->balance - $total;
        $company->save();
        return true;
    }
}
