<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\CompanyDetail;
use App\Models\LectureDetail;
use App\Models\StudentDetail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function index($role) {
        $users = [];
        if ($role == 'student') {
            $users = User::getStudents();
        }elseif ($role == 'lecture') {
            $users = User::getLectures();
        }elseif ($role == 'college') {
            $users = User::getColleges();
        }elseif ($role == 'company') {
            $users = User::getCompanies();
        }else {
            return response()->json([
                'message' => 'Role tidak ditemukan.',
                'data' => []
            ], 404);
        }
        return response()->json([
            'message' => 'Berhasil memuat data.',
            'data' => $users
        ]);
    }

    public function show($id) {
        $user = User::find($id);
        if ($user) {
            return response()->json([
                'message' => 'Berhasil memuat data.',
                'data' => $user->getDetail(),
            ]);
        }
        return response()->json([
            'message' => 'Pengguna tidak ditemukan.',
        ], 404);
    }

    public function getStudents(Request $request, $college_id) {
        $user = User::find($college_id);
        if ($user) {
            if ($user->role == 'college') {
                $students = [];
                $ids = User::join('student_details', 'student_details.user_id', '=', 'users.id')
                ->where('college_id', $college_id);
                if ($request->status == 'accepted') {
                    $ids = $ids->where('status', 'accepted');
                }
                foreach ($ids->pluck('users.id') as $id) $students[] = User::find($id)->getDetail();
                return response()->json([
                    "message" => "Berhasil memuat data.",
                    "data" => $students
                ]);
            }
            return response()->json([
                "message" => "Kampus tidak ditemukan.",
                "data" => null
            ], 404);
        }
        return response()->json([
            "message" => "Kampus tidak ditemukan.",
            "data" => null
        ], 404);
    }

    public function getLectures(Request $request, $college_id) {
        $user = User::find($college_id);
        if ($user) {
            if ($user->role == 'college') {
                $lectures = [];
                $ids = User::join('lecture_details', 'lecture_details.user_id', '=', 'users.id')
                ->where('college_id', $college_id);
                if ($request->status == 'accepted') {
                    $ids = $ids->where('status', 'accepted');
                }
                foreach ($ids->pluck('users.id') as $id) $lectures[] = User::find($id)->getDetail();
                return response()->json([
                    "message" => "Berhasil memuat data.",
                    "data" => $lectures
                ]);
            }
            return response()->json([
                "message" => "Kampus tidak ditemukan.",
                "data" => null
            ], 404);
        }
        return response()->json([
            "message" => "Kampus tidak ditemukan.",
            "data" => null
        ], 404);
    }

    public function updateProfile(Request $request) {
        if (Auth::user()->role == 'student') return $this->updateStudent($request, Auth::user()->id);
        else if (Auth::user()->role == 'lecture') return $this->updateLecture($request, Auth::user()->id);
        else if (in_array(Auth::user()->role, ['company', 'college'])) return $this->updateCompany($request, Auth::user()->id);
    }

    public function updateStudent(Request $request, $id) {
        $val = Validator::make($request->all(), [
            'name' => 'required|string|min:3',
            'address' => 'required|string|min:3',
            'phone' => 'required|numeric|min:3',
            'picture' => 'nullable|image|mimes:jpeg,png,jpg|max:8192|dimensions:ratio=1/1',
        ]);
        if ($val->fails()) {
            return response()->json([
                'message' => 'Bidang tidak valid.',
                'data' => $val->errors(),
            ], 403);
        }
        $user = User::find($id);
        if ($user) {
            $filename = $user->picture;
            $file = $request->file('picture');
            if ($file) {
                if (file_exists('/uploads/'.$filename)) {
                    unlink('/uploads/'.$filename);
                }
                $filename = time() . rand(1111,9999) . '.' . $file->getClientOriginalExtension();
                $file->move("uploads/", $filename);
            }
            $user->name = trim(strtolower($request->name));
            $user->picture = $filename;
            $user->save();
            $detail = StudentDetail::where('user_id', $user->id)->first();
            $detail->address = trim($request->address);
            $detail->phone = trim($request->phone);
            $detail->save();
            return response()->json([
                'message' => 'Perubahan berhasil disimpan.',
                'data' => $user->getDetail(),
            ]);
        }
        return response()->json([
            'message' => 'Pengguna tidak ditemukan.',
            'data' => null
        ], 404);
    }

    public function updateLecture(Request $request, $id) {
        $val = Validator::make($request->all(), [
            'name' => 'nullable|string|min:3',
            'address' => 'nullable|string|min:3',
            'phone' => 'nullable|numeric|min:3',
            'picture' => 'nullable|image|mimes:jpeg,png,jpg|max:8192|dimensions:ratio=1/1',
        ]);
        if ($val->fails()) {
            return response()->json([
                'message' => 'Bidang tidak valid.',
                'data' => $val->errors(),
            ], 403);
        }
        $user = User::find($id);
        if ($user) {
            $filename = $user->picture;
            $file = $request->file('picture');
            if ($file) {
                if (file_exists('/uploads/'.$filename)) {
                    unlink('/uploads/'.$filename);
                }
                $filename = time() . rand(1111,9999) . '.' . $file->getClientOriginalExtension();
                $file->move("uploads/", $filename);
            }
            $user->name = trim(strtolower($request->name));
            $user->picture = $filename;
            $user->save();
            $detail = LectureDetail::where('user_id', $user->id)->first();
            $detail->address = trim($request->address);
            $detail->phone = trim($request->phone);
            $detail->save();
            return response()->json([
                'message' => 'Perubahan berhasil disimpan.',
                'data' => $user->getDetail(),
            ]);
        }
        return response()->json([
            'message' => 'Pengguna tidak ditemukan.',
            'data' => null
        ], 404);
    }

    public function updateCompany(Request $request, $id) {
        $val = Validator::make($request->all(), [
            'name' => 'nullable|string|min:3',
            'description' => 'nullable|string|min:3',
            'address' => 'nullable|string|min:3',
            'phone' => 'nullable|numeric|min:3',
            'picture' => 'nullable|image|mimes:jpeg,png,jpg|max:8192|dimensions:ratio=1/1',
        ]);
        if ($val->fails()) {
            return response()->json([
                'message' => 'Bidang tidak valid.',
                'data' => $val->errors(),
            ], 403);
        }
        $user = User::find($id);
        if ($user) {
            $filename = $user->picture;
            $file = $request->file('picture');
            if ($file) {
                if (file_exists('/uploads/'.$filename)) {
                    unlink('/uploads/'.$filename);
                }
                $filename = time() . rand(1111,9999) . '.' . $file->getClientOriginalExtension();
                $file->move("uploads/", $filename);
            }
            $user->name = trim(strtolower($request->name));
            $user->picture = $filename;
            $user->save();
            $detail = CompanyDetail::where('user_id', $user->id)->first();
            $detail->description = trim($request->description);
            $detail->address = trim($request->address);
            $detail->phone = trim($request->phone);
            $detail->save();
            return response()->json([
                'message' => 'Perubahan berhasil disimpan.',
                'data' => $user->getDetail(),
            ]);
        }
        return response()->json([
            'message' => 'Pengguna tidak ditemukan.',
            'data' => null
        ], 404);
    }
}
