<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Logbook extends Model
{
    use HasFactory;

    public $fillable = [
        'submited_at', 'description', 'student_id', 'project_id'
    ];

    public function getDetail() {
        $logbook = Logbook::find($this->id);
        $logbook->student = User::find($this->student_id)->getDetail();
        $logbook->project = Project::find($this->project_id)->getDetail();
        unset($logbook->student_id);
        unset($logbook->project_id);
        return $logbook;
    }
}
