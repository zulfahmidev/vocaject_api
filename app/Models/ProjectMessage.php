<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectMessage extends Model
{
    use HasFactory;

    public $fillable = [
        'message', 'sender', 'project_id'
    ];

    public function getDetail() {
        $message = ProjectMessage::find($this->id);
        $message->project = Project::find($this->project_id)->getDetail();
        unset($message->project_id);
        return $message;
    }
}
