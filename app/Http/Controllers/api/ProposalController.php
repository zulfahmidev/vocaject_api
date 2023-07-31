<?php

namespace App\Http\Controllers\api;

use App\Events\SubmitProposal;
use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Proposal;
use App\Models\ProposalAttachment;
use App\Models\ProposalMember;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProposalController extends Controller
{
    public function index(Request $request, $project_id) {
        $raw = Proposal::where('project_id', $project_id);
        if ($request->lecture_id) {
            $raw->where('lecture_id', $request->lecture_id);
        }
        $proposals = [];
        foreach ($raw->get() as $proposal) {
            $proposals[] = $proposal->getDetail();
        }
        return response()->json([
            'message' => 'Berhasil memuat data.',
            'data' => $proposals
        ]);
    }

    public function store(Request $request, $project_id) {
        $val = Validator::make($request->all(), [
            'note' => 'required|min:3',
            'lecture_id' => 'required|exists:users,id',
            'student_ids' => 'required|array|exists:users,id',
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
        $proposal = Proposal::where('lecture_id', $request->lecture_id)->where('project_id', $project_id)->first();
        if ($proposal) {
            return response()->json([
                'message' => 'Anda sudah mengajukan proposal sebelumnya.',
                'data' => null,
            ], 403);
        }
        $proposal = Proposal::create([
            'note' => $request->note,
            'lecture_id' => $request->lecture_id,
            'project_id' => $project->id,
            'status' => 'panding',
        ]);
        foreach ($request->student_ids as $student_id) {
            // dd($student_id);
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
                'filename' => $file->getClientOriginalName(),
                'mimetype' => $file->getClientOriginalExtension(),
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
                'filename' => $file->getClientOriginalName(),
                'mimetype' => $file->getClientOriginalExtension(),
                'filepath' => $filename,
            ]);
        }
        if ($request->file('additional2_attachment')) {
            $file = $request->file('additional2_attachment');
            $dir = 'uploads/';
            $filename = time().rand(1111,9999).'.'.$file->getClientOriginalExtension();
            $file->move($dir, $filename);
            ProposalAttachment::create([
                'proposal_id' => $proposal->id,
                'filename' => $file->getClientOriginalName(),
                'mimetype' => $file->getClientOriginalExtension(),
                'filepath' => $filename,
            ]);
        }

        // event(new SubmitProposal($proposal));

        return response()->json([
            'message' => 'Berhasil memuat data.',
            'data' => $proposal->getDetail()
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


    public function showByLecture($project_id, $lecture_id) {
        $project = Project::find($project_id);
        if (!$project) {
            return response()->json([
                'message' => 'Proyek tidak ditemukan.',
                'data' => null
            ], 404);
        }
        $proposal = Proposal::where('lecture_id', $lecture_id)->first();
        // dd($proposal);
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

    public function getProposalAccepted($project_id) {
        $project = Project::find($project_id);
        if (!$project) {
            return response()->json([
                'message' => 'Proyek tidak ditemukan.',
                'data' => null
            ], 404);
        }
        $proposal = $project->getAccProposal();
        if ($proposal) {
            return response()->json([
                'message' => 'Berhasil memuat data.',
                'data' => $proposal->getDetail(),
            ]);
        }
        return response()->json([
            'message' => 'Belum ada proposal yang dikonfirmasi.',
            'data' => null,
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
                $proposal->update([
                    'status' => 'accepted'
                ]);
                $project->transferBudget();
                continue;
            }
            $proposal->update([
                'status' => 'rejected'
            ]);
        }
        return response()->json([
            'message' => 'Proposal berhasil diterima.',
            'data' => $project->getDetail()
        ]);
    }
}