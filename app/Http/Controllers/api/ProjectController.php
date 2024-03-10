<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectBudget;
use App\Models\ProjectCategory;
use App\Models\Proposal;
use App\Models\ProposalMember;
use App\Models\User;
use Carbon\Carbon;
use Faker\Core\Number;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ProjectController extends Controller
{

    public function index(Request $request) {
        $raw = DB::table('projects');
        if ($request->student_id) {
            $raw = Proposal::join('projects', 'projects.id', '=', 'proposals.project_id')
            ->join('proposal_members', 'proposal_members.proposal_id', '=', 'proposals.id')
            ->where('proposal_members.student_id', $request->student_id);
            // ->select(DB::raw('proposal_members'));
            if ($request->proposal_status) {
                $raw = $raw->where('proposals.status', $request->proposal_status);
            }
        }elseif ($request->lecture_id) {
            $raw = Proposal::join('projects', 'projects.id', '=', 'proposals.project_id')
            ->where('proposals.lecture_id', $request->lecture_id);
            if ($request->proposal_status) {
                $raw = $raw->where('proposals.status', $request->proposal_status);
            }
        }
        // if (is_numeric($request->offset)) {
        //     $raw = $raw->skip((int)$request->offset);
        // }
        // if (is_numeric($request->take)) {
        //     $raw = $raw->take((int)$request->take);
        // }
        if ($request->category) {
            $category = ProjectCategory::where('slug', trim($request->category))->first();
            if ($category) {
                $raw = $raw->where('category_id', $category->id);
            }
        }
        if ($request->title) {
            $raw = $raw->where('title', 'like', "%".strtolower(trim($request->title))."%");
        }
        if ($request->company_id) {
            $raw = $raw->where('company_id', '=', $request->company_id);
        }
        if ($request->latest) {
            $raw->latest('created_at');
        }
        $projects = [];
        $raw = ($request->has('student_id') || $request->has('lecture_id')) ? $raw->selectRaw('projects.id, proposals.status, proposals.id as proposal_id')->get() : $raw->selectRaw('projects.id')->get();
        foreach ($raw as $v) {
            $project = Project::find($v->id)->getDetail();
            if (isset($v->proposal_id)) {
                $project->members = ProposalMember::where('proposal_id', $v->proposal_id)
                ->join('users', 'users.id', '=', 'proposal_members.student_id')
                ->join('student_details', 'student_details.user_id', '=', 'proposal_members.student_id')
                ->selectRaw('users.id, users.name, users.email, users.picture, student_details.phone')
                ->get();
            }
            if ($request->has('student_id') || $request->has('lecture_id')) {
                $project->proposal_status = $v->status;
            }
            if ($request->status) {
                if ($project->status == $request->status) {
                    $projects[] = $project;
                }
            }else {
                $projects[] = $project;
            }
        };
        $skip = 0;
        $take = null;
        if (is_numeric($request->offset)) {
            $skip = (int)$request->offset;
        }
        if (is_numeric($request->take)) {
            $take = (int)$request->take;
        }
        if ($take) {
            $projects = array_splice($projects, $skip, $take);
        }
        return response()->json([
            'message' => 'Berhasil memuat data.',
            'data' => $projects,
        ]);
    }

    public function show($project_id) {
        $project = Project::find($project_id);
        if (!$project) {
            return response()->json([
                'message' => 'Proyek tidak ditemukan.',
                'data' => null,
            ], 404);
        }
        return response()->json([
            'message' => 'Berhasil memuat data.',
            'data' => $project->getDetail(),
        ]);
    }

    public function store(Request $request) {
        $val = Validator::make($request->all(), [
            'company_id' => 'required|exists:users,id',
            'title' => 'required|min:3',
            'expired_at' => 'required|date_format:m-d-Y',
            'deadline_at' => 'required|date_format:m-d-Y',
            'description' => 'required|min:3',
            'budget' => 'required|numeric',
            'category_id' => 'required|exists:project_categories,id',
        ]);
        if ($val->fails()) {
            return response()->json([
                'message' => 'Bidang tidak valid.',
                'data' => $val->errors(),
            ], 400);
        }
        $project = Project::create([
            'company_id' => $request->company_id,
            'title' => trim(strtolower($request->title)),
            'description' => $request->description,
            'expired_at' => $this->timestampFormat($request->expired_at),
            'deadline_at' => $this->timestampFormat($request->deadline_at),
            'budget' => (int) $request->budget,
            'category_id' => $request->category_id,
        ]);
        return response()->json([
            'message' => 'Proyek berhasil dibuat.',
            'data' => $project->getDetail(),
        ]);
    }

    private function timestampFormat($date) {
        $date = explode('-', $date);
        return Carbon::create($date[2], $date[0], $date[1]);
    }

    public function update(Request $request, $id) {
        $val = Validator::make($request->all(), [
            'title' => 'required|min:3',
            'expired_at' => 'required|date_format:m-d-Y',
            'deadline_at' => 'required|date_format:m-d-Y',
            'description' => 'required|min:3',
            'budget' => 'required|numeric',
            'category_id' => 'required|exists:project_categories,id',
        ]);
        if ($val->fails()) {
            return response()->json([
                'message' => 'Bidang tidak valid.',
                'data' => $val->errors(),
            ], 400);
        }
        $project = Project::find($id);
        if ($project) {
            $project->update([
                'company_id' => $request->company_id,
                'title' => trim(strtolower($request->title)),
                'expired_at' => $this->timestampFormat($request->expired_at),
                'deadline_at' => $this->timestampFormat($request->deadline_at),
                'description' => $request->description,
                'budget' => (int) $request->budget,
                'category_id' => $request->category_id,
            ]);
            return response()->json([
                'message' => 'Proyek berhasil diubah.',
                'data' => $project->getDetail(),
            ]);
        }
        return response()->json([
            'message' => 'Proyek tidak ditemukan.',
            'data' => null,
        ], 404);
    }

    public function destroy($id) {
        $project = Project::find($id);
        if ($project) {
            if ($project->getDetail()->status == 'closed') {
                return response()->json([
                    'message' => 'Proyek tidak dapat dihapus.',
                    'data' => null,
                ], 403);
            }
            $project->delete();
            return response()->json([
                'message' => 'Proyek berhasil dihapus.',
                'data' => $project,
            ]);
        }
        return response()->json([
            'message' => 'Proyek tidak ditemukan.',
            'data' => null,
        ], 404);
    }

    public function manageBudget(Request $request, $project_id) {
        $val = Validator::make($request->all(), [
            'student' => 'required|numeric',
            'lecture' => 'required|numeric',
            'college' => 'required|numeric',
        ]);
        if ($val->fails()) {
            return response()->json([
                'message' => 'Bidang tidak valid.',
                'data' => $val->errors()
            ], 400);
        }
        $project = Project::find($project_id);
        if ($project) {
            if (ProjectBudget::where('project_id', $project->id)->first()) {
                return response()->json([
                    'message' => 'Anda sudah mengelola anggaran.',
                    'data' => null
                ], 403);
            }
            $proposal = $project->getAccProposal();
            if ($proposal) {

                $students = ProposalMember::where('proposal_id', $proposal->id)->get();
                $cost_student = (int) $request->student * $students->count();
                $cost_lecture = (int) $request->lecture;
                $cost_college = (int) $request->college;
                $total = $cost_college + $cost_lecture + $cost_student;
                if ($project->budget > $total) {
                    $remaining = $project->budget - $total;
                    $lecture = User::find($proposal->lecture_id);
                    $lecture->balance = $lecture->balance + $cost_lecture;
                    $lecture->save();
                    // Notifikasi email

                    $college = User::find($lecture->getDetail()->college->id);
                    $college->balance = $college->balance + $cost_college;
                    $college->save();
                    // Notifikasi email

                    foreach ($students as $member) {
                        $student = User::find($member->student_id);
                        $student->balance = $student->balance + $cost_student;
                        $student->save();
                        // Notifikasi email
                    }

                    $pb = ProjectBudget::create([
                        'project_id' => $project->id,
                        'student' => $cost_student,
                        'lecture' => $cost_lecture,
                        'college' => $cost_college,
                        'remaining' => $remaining,
                    ]);
                    return response()->json([
                        'message' => 'Anggaran berhasil disalurkan.',
                        'data' => $pb
                    ]);
                }
                return response()->json([
                    'message' => 'Anggaran tidak cukup untuk pembagian ini.',
                    'data' => null
                ], 403);
            }
            return response()->json([
                'message' => 'Belum ada proposal yang diterima.',
                'data' => null
            ], 404);
        }
        return response()->json([
            'message' => 'Proyek tidak ditemukan.',
            'data' => null
        ], 404);
    }
}
