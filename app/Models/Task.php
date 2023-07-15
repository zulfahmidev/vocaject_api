<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    public $fillable = [
        'project_id', 'title', 'description', 'checked'
    ];

    public function getDetail() {
        $task = Task::find($this->id);
        $task->project = Project::find($this->project_id)->getDetail();
        $task->checked = (bool)$task->checked;
        unset($task->project_id);
        return $task;
    }
}
