<?php

namespace App\Http\Controllers\api;

use App\Events\DeleteMessage;
use App\Events\NewMessage;
use App\Http\Controllers\Controller;
use App\Models\DocumentUserPermission;
use App\Models\Project;
use App\Models\ProjectMessage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProjectMessageController extends Controller
{
    public function index($project_id, $lecture_id) {
        $project = Project::find($project_id);
        if (!$project) {
            return response()->json([
                'message' => 'Proyek tidak ditemukan.',
                'data' => null,
            ], 404);
        }
        $lecture = User::find($lecture_id);
        if (!$lecture) {
            return response()->json([
                'message' => 'Dosen tidak ditemukan.',
                'data' => null,
            ], 404);
        }
        $raw = ProjectMessage::where('project_id', $project_id)->where('lecture_id', $lecture_id)->get();
        $messages = [];
        foreach ($raw as $message) {
            $messages[] = $message->getDetail();
        }
        return response()->json([
            'message' => 'Berhasil memuat data.',
            'data' => $messages,
        ]);
    }

    public function store(Request $request, $project_id, $lecture_id) {
        $project = Project::find($project_id);
        if (!$project) {
            return response()->json([
                'message' => 'Proyek tidak ditemukan.',
                'data' => null,
            ], 404);
        }
        $lecture = User::find($lecture_id);
        if (!$lecture) {
            return response()->json([
                'message' => 'Dosen tidak ditemukan.',
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
            'lecture_id' => $lecture->id,
            'message' => json_encode([
                "type" => "text",
                "text" => $request->message
            ]),
            'sender' => $request->sender,
        ]);

        event(new NewMessage($message));

        return response()->json([
            'message' => 'Pesan berhasil terkirim.',
            'data' => $message->getDetail(),
        ]);
    }


    public function storeDocument(Request $request, $project_id, $lecture_id) {
        $project = Project::find($project_id);
        if (!$project) {
            return response()->json([
                'message' => 'Proyek tidak ditemukan.',
                'data' => null,
            ], 404);
        }
        $lecture = User::find($lecture_id);
        if (!$lecture) {
            return response()->json([
                'message' => 'Dosen tidak ditemukan.',
                'data' => null,
            ], 404);
        }
        $val = Validator::make($request->all(), [
            'sender' => 'required|string|in:lecture,company,college',
            'file' => 'required|file'
        ]);

        if ($val->fails()) {
            return response()->json([
                'message' => 'Bidang tidak valid.',
                'data' => $val->errors(),
            ], 400);
        }

        $doc = DocumentController::upload($request->file('file'), 'private');
        if ($doc) {

            $message = ProjectMessage::create([
                'project_id' => $project->id,
                'lecture_id' => $lecture->id,
                'message' => json_encode([
                    "type" => "document",
                    "document" => $doc->getData()
                ]),
                'sender' => $request->sender,
            ]);

            DocumentUserPermission::insert([
                ["user_id" => $lecture->id, "document_id" => $doc->id],
                ["user_id" => $project->company_id, "document_id" => $doc->id],
            ]);

            event(new NewMessage($message));

            return response()->json([
                'message' => 'Pesan berhasil terkirim.',
                'data' => $message->getDetail(),
            ]);
        }

        return response()->json([
            "message" => "Terjadi kesalahan saat mengupload",
            "data" => null
        ], 500);

    }

    public function getCompanyContacts($project_id) {
        $raw = ProjectMessage::where('project_id', $project_id)->pluck('lecture_id')->toArray();
        $lecture_ids = array_unique($raw);
        $contacts = [];
        foreach ($lecture_ids as $lecture_id) {
            $user = User::find($lecture_id);
            if ($user) {
                $contacts[] = $user->getDetail();
            }
        }
        return response()->json([
            'message' => 'Berhasil memuat data',
            'data' => $contacts,
        ]);
    }

    public function getLectureContacts($lecture_id) {
        $raw = ProjectMessage::where('lecture_id', $lecture_id)->pluck('project_id')->toArray();
        $project_ids = array_unique($raw);
        $contacts = [];
        foreach ($project_ids as $project_id) {
            $project = Project::find($project_id);
            if ($project) {
                $contacts[] = $project->getDetail()->company;
            }
        }
        return response()->json([
            'message' => 'Berhasil memuat data',
            'data' => $contacts,
        ]);
    }

    public function destroy($project_id, $lecture_id, $message_id) {
        $project = Project::find($project_id);
        if (!$project) {
            return response()->json([
                'message' => 'Proyek tidak ditemukan.',
                'data' => null,
            ], 404);
        }
        $lecture = User::find($lecture_id);
        if (!$lecture) {
            return response()->json([
                'message' => 'Dosen tidak ditemukan.',
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

    public function read($project_id, $lecture_id) {
        $project = Project::find($project_id);
        if (!$project) {
            return response()->json([
                'message' => 'Proyek tidak ditemukan.',
                'data' => null,
            ], 404);
        }
        $lecture = User::find($lecture_id);
        if (!$lecture) {
            return response()->json([
                'message' => 'Dosen tidak ditemukan.',
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

    public function getCountUnread($project_id, $lecture_id) {
        $project = Project::find($project_id);
        if (!$project) {
            return response()->json([
                'message' => 'Proyek tidak ditemukan.',
                'data' => null,
            ], 404);
        }
        $lecture = User::find($lecture_id);
        if (!$lecture) {
            return response()->json([
                'message' => 'Dosen tidak ditemukan.',
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
