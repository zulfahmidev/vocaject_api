<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectCategory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ProjectController extends Controller
{

    public function index(Request $request) {
        $raw = DB::table('projects');
        if ($request->category) {
            $category = ProjectCategory::where('slug', trim($request->category))->pluck('id');
            $raw = $raw->whereIn('category_id', $category);
        }
        if ($request->title) {
            $raw = $raw->where('title', 'like', "%".strtolower(trim($request->title))."%");
        }
        if ($request->company_id) {
            $raw = $raw->where('company_id', '=', $request->company_id);
        }
        $projects = [];
        foreach ($raw->pluck('id') as $id) {
            $project = Project::find($id)->getDetail();
            if ($request->status) {
                if ($project->status == $request->status) {
                    $projects[] = $project;       
                }
            }else {
                $projects[] = $project;   
            }
        };
        return response()->json([
            'message' => 'Berhasil memuat data.',
            'data' => $projects,
        ]);
    }

    public function store(Request $request) {
        $val = Validator::make($request->all(), [
            'company_id' => 'required|exists:users,id',
            'title' => 'required',
            'description' => 'required',
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
            'budget' => (int) $request->budget, 
            'category_id' => $request->category_id,
        ]);
        return response()->json([
            'message' => 'Proyek berhasil dibuat.',
            'data' => $project->getDetail(),
        ]);
    }

    public function update(Request $request, $id) {
        $val = Validator::make($request->all(), [
            'title' => 'required',
            'description' => 'required',
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
                    'message' => 'Proyek tidak dihapus.',
                    'data' => null,
                ], 403);
            }
            $project->delete();
            return response()->json([
                'message' => 'Proyek berhasil dihapus.',
                'data' => $project->Detail(),
            ]);
        }
        return response()->json([
            'message' => 'Proyek tidak ditemukan.',
            'data' => null,
        ], 404);
    }
}
