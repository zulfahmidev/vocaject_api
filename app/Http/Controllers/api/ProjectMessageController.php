<?php

namespace App\Http\Controllers\api;

use App\Events\DeleteMessage;
use App\Events\NewMessage;
use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProjectMessageController extends Controller
{
    public function index($project_id) {
        $project = Project::find($project_id);
        if (!$project) {
            return response()->json([
                'message' => 'Proyek tidak ditemukan.',
                'data' => null,
            ], 404);
        }
        $raw = ProjectMessage::where('project_id', $project_id)->get();
        $messages = [];
        foreach ($raw as $message) {
            $messages[] = $message->getDetail();
        }
        return response()->json([
            'message' => 'Berhasil memuat data.',
            'data' => $messages,
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
            'sender' => 'required|string|in:lecture,company',
            'message' => 'required|string|min:1'
        ]);

        if ($val->fails()) {
            return response()->json([
                'message' => 'Bidang tidak valid.',
                'data' => $val->errors(),
            ], 400);
        }

        $message = ProjectMessage::create([
            'project_id' => $project->id,
            'message' => $request->message,
            'sender' => $request->sender
        ]);

        event(new NewMessage($message->getDetail()));

        return response()->json([
            'message' => 'Pesan berhasil terkirim.',
            'data' => $message->getDetail(),
        ]);
    }

    public function destroy($project_id, $message_id) {
        $project = Project::find($project_id);
        if (!$project) {
            return response()->json([
                'message' => 'Proyek tidak ditemukan.',
                'data' => null,
            ], 404);
        }
        $message = ProjectMessage::find($message_id);
        if (!$message) {
            return response()->json([
                'message' => 'Pesan tidak ditemukan.',
                'data' => null,
            ], 404);
        }

        event(new DeleteMessage($message));
        
        $message->delete();


        return response()->json([
            'message' => 'Pesan berhasil dihapus.',
            'data' => $message,
        ]);
    }

    public function read($project_id) {
        $project = Project::find($project_id);
        if (!$project) {
            return response()->json([
                'message' => 'Proyek tidak ditemukan.',
                'data' => null,
            ], 404);
        }
        $messages = ProjectMessage::where('project_id', $project_id)->where('read_at', null)->get();
        foreach ($messages as $message) {
            $message->read_at = now();
            $message->save();
        }
        return response()->json([
            'message' => 'Pesan berhasil dibaca.',
            'data' => null,
        ]);
    }

    public function getCountUnread($project_id) {
        $project = Project::find($project_id);
        if (!$project) {
            return response()->json([
                'message' => 'Proyek tidak ditemukan.',
                'data' => null,
            ], 404);
        }
        $messages = ProjectMessage::where('project_id', $project_id)->where('read_at', null)->get();
        return response()->json([
            'message' => 'Berhasil memuat data.',
            'data' => $messages->count(),
        ]);
    }
}
