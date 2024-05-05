<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\DocumentUserPermission;
use Google\Cloud\Storage\StorageClient;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Nette\Utils\Random;

class DocumentController extends Controller
{
    public function view($filename) {
        $doc = Document::where('filename', $filename)->first();
        if ($doc) {
            if (!$this->authorityCheck($doc)) {
                return response()->json([
                    "message" => "Akses ditolak",
                    "data" => null
                ], 403);
            }
            $credentials = file_get_contents(base_path('credentials.json'));
            $storage = new StorageClient([
                'keyFile' => json_decode($credentials, true)
            ]);
            $bucket = $storage->bucket(env('GOOGLE_CLOUD_STORAGE_BUCKET'));
            $object = $bucket->object('uploads/'.$filename);
            $content = $object->downloadAsString();

            return response($content, 200, [
                'Content-Type' => $doc->mimetype
            ]);
        }
        return response()->json([
            "message" => "Dokumen tidak ditemukan",
            "data" => null
        ], 404);
    }

    public function detail($filename) {
        $doc = Document::where('filename', $filename)->first();
        if ($doc) {
            if (!$this->authorityCheck($doc)) {
                return response()->json([
                    "message" => "Akses ditolak",
                    "data" => null
                ], 403);
            }

            return response()->json([
                "message" => "Berhasil memuat dokumen",
                "data" => $doc->getData()
            ], 200);
        }
        return response()->json([
            "message" => "Dokumen tidak ditemukan",
            "data" => null
        ], 404);
    }

    public static function upload(UploadedFile $file, string $visibility, Array $user_ids = []) {
        $filename = explode(".", $file->hashName())[0];

        $credentials = file_get_contents(base_path('credentials.json'));
        $storage = new StorageClient([
            'keyFile' => json_decode($credentials, true)
        ]);
        $bucket = $storage->bucket(env('GOOGLE_CLOUD_STORAGE_BUCKET'));
        $res = $bucket->upload($file->getContent(), [
            'name' => 'uploads/'.$filename
        ]);

        if ($res) {
            $doc = Document::create([
                "filename" => $filename,
                "origin_filename" => $file->getClientOriginalName(),
                "mimetype" => $file->getClientMimeType(),
                "extension" => $file->getClientOriginalExtension(),
                "visibility" => $visibility
            ]);

            foreach ($user_ids as $user_id) {
                DocumentUserPermission::create([
                    "user_id" => $user_id,
                    "document_id" => $doc->id,
                ]);
            }

            return $doc;
        }
        return false;
    }

    public function authorityCheck(Document $document) {
        if ($document->visibility == 'private') {
            if (Auth::check()) {
                $user = auth()->user();
                $checkPermission = DocumentUserPermission::where('user_id', $user->id)
                ->where('document_id', $document->id)->first();
                if ($checkPermission) {
                    return true;
                }
            }
            return false;
        }
        return true;
    }
}
