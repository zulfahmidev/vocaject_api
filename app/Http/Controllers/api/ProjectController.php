<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProjectController extends Controller
{

    public function index() {
        $projects = [];
        foreach (Project::all() as $project) $projects[] = $project->getDetail();
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
            'data' => $project,
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
                'data' => $project,
            ]);
        }
        return response()->json([
            'message' => 'Proyek tidak ditemukan.',
        ], 404);
    }

    public function destroy($id) {
        $project = Project::find($id);
        if ($project) {
            $project->delete();
            return response()->json([
                'message' => 'Proyek berhasil dihapus.',
                'data' => $project,
            ]);
        }
        return response()->json([
            'message' => 'Proyek tidak ditemukan.',
        ], 404);
    }
}
