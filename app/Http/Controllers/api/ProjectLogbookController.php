<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\ProjectLogbook;
use App\Models\Project;
use App\Models\ProposalMember;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProjectLogbookController extends Controller
{
    public function index($project_id, $student_id) {
        $project = Project::find($project_id);
        if (!$project) {
            return response()->json([
                'message' => 'Proyek tidak ditemukan.',
                'data' => null,
            ], 404);
        }
        $student = User::find($student_id);
        if (!$student) {
            return response()->json([
                'message' => 'Mahasiswa tidak ditemukan.',
                'data' => null,
            ], 404);
        }
        $raw = ProjectLogbook::where('project_id', $project->id)->where('student_id', $student->id)->get();
        $logbooks = [];
        foreach ($raw as $logbook) {
            $logbooks[] = $logbook->getDetail();
        }
        return response()->json([
            'message' => 'Berhasil memuat data.',
            'data' => $logbooks,
        ]);
    }

    public function store(Request $request, $project_id, $student_id) {
        $project = Project::find($project_id);
        if (!$project) {
            return response()->json([
                'message' => 'Proyek tidak ditemukan.',
                'data' => null,
            ], 404);
        }
        $student = User::find($student_id);
        if (!$student) {
            return response()->json([
                'message' => 'Mahasiswa tidak ditemukan.',
                'data' => null,
            ], 404);
        }
        $member = ProposalMember::where('student_id', $student->id)
        ->where('proposal_id', $project->getAccProposal()?->id)
        ->first();
        if (!$member) {
            return response()->json([
                'message' => 'Mahasiswa tidak termasuk dalam tim proyek.',
                'data' => null,
            ], 404);
        }
        $val = Validator::make($request->all(), [
            'submited_at' => 'required|date_format:m-d-Y',
            'description' => 'required|min:3'
        ]);

        if ($val->fails()) {
            return response()->json([
                'message' => 'Bidang tidak valid.',
                'data' => $val->errors(),
            ], 400);
        }
        $logbook = ProjectLogbook::create([
            'description' => trim($request->description),
            'submited_at' => $this->timestampFormat($request->submited_at),
            'project_id' => $project->id,
            'student_id' => $student->id,
        ]);
        return response()->json([
            'message' => 'Berhasil memuat data.',
            'data' => $logbook->getDetail(),
        ]);
    }

    private function timestampFormat($date) {
        $date = explode('-', $date);
        return Carbon::create((int)$date[2], (int)$date[0], (int)$date[1]);
    }
}
