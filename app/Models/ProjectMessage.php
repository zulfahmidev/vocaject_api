<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectMessage extends Model
{
    use HasFactory;

    public $fillable = [
        'message', 'sender', 'project_id', 'lecture_id'
    ];

    public function getDetail() {
        $message = ProjectMessage::find($this->id);
        $message->project = Project::find($this->project_id)->getDetail();
        $message->lecture = User::find($this->lecture_id)->getDetail();
        $message->message = json_decode($this->message, true);
        unset($message->project_id);
        unset($message->lecture_id);
        return $message;
    }
}
