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
        $project->company = User::find($project->company_id)->getDetail();
        $project->category = ProjectCategory::find($project->category_id);
        $project->status = $this->getStatus();
        unset($project->company_id);
        unset($project->category_id);
        return $project;
    }

    public function getStatus() {
        $proposal = Proposal::where('project_id', $this->id)->where('status', 'accepted')->first();
        return ($proposal) ? 'closed' : 'opened';
    }
}
