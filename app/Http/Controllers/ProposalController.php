<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Proposal;
use App\Models\ProposalAttachment;
use App\Models\ProposalMember;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProposalController extends Controller
{
    public function index(Request $request, $project_id) {
        $proposals = Proposal::where('project_id', $project_id);
        if ($request->lecture_id) {
            $proposals->where('lecture_id', $request->lecture_id);
        }
        return response()->json([
            'message' => 'Berhasil memuat data.',
            'data' => $proposals->get()
        ]);
    }

    public function store(Request $request, $project_id) {
        $val = Validator::make($request->all(), [
            'note' => 'required',
            'lecture_id' => 'required',
            // 'project_id' => 'required',
            'students' => 'required|array',
            'mandatory_attachment' => 'required|file',
            'additional1_attachment' => 'nullable|file',
            'additional2_attachment' => 'nullable|file',
        ]);
        if ($val->fails()) {
            return response()->json([
                'message' => 'Bidang tidak valid.',
                'data' => $val->errors()
            ], 400);
        }
        $project = Project::find($project_id);
        if (!$project) {
            return response()->json([
                'message' => 'Proyek tidak ditemukan.',
                'data' => null,
            ], 404);
        }
        $proposal = Proposal::create([
            'note' => $request->note,
            'lecture_id' => $request->lecture_id,
            'project_id' => $project->id,
            'status' => 'panding',
        ]);
        foreach ($request->students as $student_id) {
            ProposalMember::create([
                'proposal_id' => $proposal->id,
                'student_id' => $student_id,
            ]);
        }
        if ($request->file('mandatory_attachment')) {
            $file = $request->file('mandatory_attachment');
            $dir = 'uploads/';
            $filename = time().rand(1111,9999).'.'.$file->getClientOriginalExtension();
            $file->move($dir, $filename);
            ProposalAttachment::create([
                'proposal_id' => $proposal->id,
                'filepath' => $filename,
            ]);
        }
        if ($request->file('additional1_attachment')) {
            $file = $request->file('additional1_attachment');
            $dir = 'uploads/';
            $filename = time().rand(1111,9999).'.'.$file->getClientOriginalExtension();
            $file->move($dir, $filename);
            ProposalAttachment::create([
                'proposal_id' => $proposal->id,
                'filepath' => $filename,
            ]);
        }
        if ($request->file('additional1_attachment')) {
            $file = $request->file('additional1_attachment');
            $dir = 'uploads/';
            $filename = time().rand(1111,9999).'.'.$file->getClientOriginalExtension();
            $file->move($dir, $filename);
            ProposalAttachment::create([
                'proposal_id' => $proposal->id,
                'filepath' => $filename,
            ]);
        }
        return response()->json([
            'message' => 'Berhasil memuat data.',
            'data' => $proposal
        ]);
    }

    public function show($project_id, $proposal_id) {
        $project = Project::find($project_id);
        if (!$project) {
            return response()->json([
                'message' => 'Proyek tidak ditemukan.',
                'data' => null
            ], 404);
        }
        $proposal = Proposal::find($proposal_id);
        if (!$proposal) {
            return response()->json([
                'message' => 'Proposal tidak ditemukan.',
                'data' => null
            ], 404);
        }
        return response()->json([
            'message' => 'Berhasil memuat data.',
            'data' => $proposal->getDetail(),
        ]);
    }

    public function confirm($project_id, $proposal_id) {
        $project = Project::find($project_id);
        if (!$project) {
            return response()->json([
                'message' => 'Proyek tidak ditemukan.',
                'data' => null
            ], 404);
        }
        $proposal = Proposal::find($proposal_id);
        if (!$proposal) {
            return response()->json([
                'message' => 'Proposal tidak ditemukan.',
                'data' => null
            ], 404);
        }
        foreach (Proposal::where('project_id', $project->id)->get() as $proposal) {
            if ($proposal->id == $proposal_id) {
                $proposal::update([
                    'status' => 'accepted'
                ]);
                continue;
            }
            $proposal::update([
                'status' => 'rejected'
            ]);
        }
        return response()->json([
            'message' => 'Proposal berhasil diterima.',
            'data' => $project
        ]);
    }
}