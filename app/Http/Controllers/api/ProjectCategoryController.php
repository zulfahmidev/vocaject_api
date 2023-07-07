<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProjectCategoryController extends Controller
{
    public function index() {
        return response()->json([
            'message' => 'Berhasil memuat data.',
            'data' => ProjectCategory::all(),
        ]);
    }

    public function store(Request $request) {
        $val = Validator::make($request->all(), [
            'name' => 'required|unique:project_categories,id'
        ]);
        if ($val->fails()) {
            return response()->json([
                'message' => 'Bidang tidak valid.',
                'data' => $val->errors(),
            ], 400);
        }
        $category = ProjectCategory::create([
            'name' => strtolower($request->name)
        ]);
        return response()->json([
            'message' => 'Kategori berhasil dibuat.',
            'data' => $category,
        ]);
    }

    public function update(Request $request, $id) {
        $val = Validator::make($request->all(), [
            'name' => "required|unique:project_categories,name"
        ]);
        if ($val->fails()) {
            return response()->json([
                'message' => 'Bidang tidak valid.',
                'data' => $val->errors(),
            ], 400);
        }
        $category = ProjectCategory::find($id);
        if ($category) {
            $category->update([
                'name' => strtolower($request->name)
            ]);
            return response()->json([
                'message' => 'Kategori berhasil diubah.',
                'data' => $category,
            ]);
        }
        return response()->json([
            'message' => 'Kategori tidak ditemukan.',
        ], 404);
    }

    public function destroy($id) {
        $category = ProjectCategory::find($id);
        if ($category) {
            $projects = Project::where('category_id', $category->id)->get();
            if ($projects->count() > 0) {
                return response()->json([
                    'message' => 'Kategori tidak dapat dihapus.',
                    'data' => null,
                ], 409);
            }
            $category->delete();
            return response()->json([
                'message' => 'Kategori berhasil dihapus.',
                'data' => $category,
            ]);
        }
        return response()->json([
            'message' => 'Kategori tidak ditemukan.',
        ], 404);
    }
}
