<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TaskController extends Controller
{
    public function index(Request $request, $project_id) {
        $project = Project::find($project_id);
        if (!$project) {
            return response()->json([
                'message' => 'Proyek tidak ditemukan.',
                'data' => null,
            ], 404);
        }
        $tasks = Task::where('project_id', $project_id);
        if ($request->has('checked')) {
            $checked = null;
            if ($request->checked == "true") {
                $checked = true;
            }else if ($request->checked == "false") {
                $checked = false;
            }
            if (!is_null($checked)) $tasks = $tasks->where('checked', $checked);
        }
        $raw = [];
        foreach ($tasks->get() as $task) $raw[] = $task->getDetail();
        return response()->json([
            'message' => 'Berhasil memuat data.',
            'data' => $raw,
        ]);
    }

    public function store(Request $request, $project_id) {
        $project = Project::find($project_id);
        if (!$project) {
            return response()->json([
                'message' => 'Proyek tidak ditemukan.',
                'data' => null,
            ], 404);
        }
        $val = Validator::make($request->all(), [
            'title' => 'required|min:3'
        ]);
        if ($val->fails()) {
            return response()->json([
                'message' => 'Bidang tidak valid.',
                'data' => $val->errors(),
            ], 400);
        }
        $task = Task::create([
            'title' => trim(strtolower($request->title)),
            'description' => '',
            'checked' => false,
            'project_id' => $project->id,
        ]);
        return response()->json([
            'message' => 'Berhasil membuat tugas.',
            'data' => $task->getDetail(),
        ]);
    }

    public function update(Request $request, $project_id, $task_id) {
        $project = Project::find($project_id);
        if (!$project) {
            return response()->json([
                'message' => 'Proyek tidak ditemukan.',
                'data' => null,
            ], 404);
        }
        $val = Validator::make($request->all(), [
            'title' => 'required|min:3',
        ]);
        if ($val->fails()) {
            return response()->json([
                'message' => 'Bidang tidak valid.',
                'data' => $val->errors(),
            ], 400);
        }
        $task = Task::find($task_id);
        if (!$task) {
            return response()->json([
                'message' => 'Tugas tidak ditemukan.',
                'data' => null,
            ], 404);
        }
        $task->update([
            'title' => trim(strtolower($request->title)),
        ]);
        return response()->json([
            'message' => 'Berhasil menyimpan perubahan.',
            'data' => $task->getDetail(),
        ]);
    }

    public function destroy($project_id, $task_id) {
        $project = Project::find($project_id);
        if (!$project) {
            return response()->json([
                'message' => 'Proyek tidak ditemukan.',
                'data' => null,
            ], 404);
        }
        $task = Task::find($task_id);
        if (!$task) {
            return response()->json([
                'message' => 'Tugas tidak ditemukan.',
                'data' => null,
            ], 404);
        }
        $task->delete();
        return response()->json([
            'message' => 'Berhasil menghapus tugas.',
            'data' => $task,
        ]);
    }

    public function switch($project_id, $task_id) {
        $project = Project::find($project_id);
        if (!$project) {
            return response()->json([
                'message' => 'Proyek tidak ditemukan.',
                'data' => null,
            ], 404);
        }
        $task = Task::find($task_id);
        if (!$task) {
            return response()->json([
                'message' => 'Tugas tidak ditemukan.',
                'data' => null,
            ], 404);
        }
        $task->update([
            'checked' => !(bool)$task->checked
        ]);
        return response()->json([
            'message' => 'Berhasil menyimpan perubahan.',
            'data' => $task->getDetail(),
        ]);
    }
}
