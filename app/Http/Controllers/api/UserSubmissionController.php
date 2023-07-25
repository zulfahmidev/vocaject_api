<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserSubmissionController extends Controller
{
    public function getStudentSubmissions($college_id) {
        $college = User::find($college_id);
        if (!$college) {
            return response()->json([
                'message' => 'Kampus tidak ditemukan.',
            ], 404);
        }
        $user = User::where('role', 'student')
        ->selectRaw('users.id, name, email, role, status')
        ->join('student_details', 'users.id', '=', 'student_details.user_id')
        ->where('college_id', $college->id);
        return response()->json([
            'message' => 'Berhasil memuat data.',
            'data' => [
                'panding' => $user->where('status', 'panding')->get(),
                'accepted' =>$user->where('status', 'accepted')->get(),
                'rejected' =>$user->where('status', 'rejected')->get(),
            ],
        ]);
    }

    public function getLectureSubmissions($college_id) {
        $college = User::find($college_id);
        if (!$college) {
            return response()->json([
                'message' => 'Kampus tidak ditemukan.',
            ], 404);
        }
        $user = User::where('role', 'lecture')
        ->selectRaw('users.id, name, email, role, status')
        ->join('lecture_details', 'users.id', '=', 'lecture_details.user_id')
        ->where('college_id', $college->id);
        return response()->json([
            'message' => 'Berhasil memuat data.',
            'data' => [
                'panding' => $user->where('status', 'panding')->get(),
                'accepted' =>$user->where('status', 'accepted')->get(),
                'rejected' =>$user->where('status', 'rejected')->get(),
            ],
        ]);
    }

    public function getCollegeSubmissions() {
        $user = User::where('role', 'college')
        ->selectRaw('users.id, name, email, role, status');
        return response()->json([
            'message' => 'Berhasil memuat data.',
            'data' => [
                'panding' => $user->where('status', 'panding')->get(),
                'accepted' =>$user->where('status', 'accepted')->get(),
                'rejected' =>$user->where('status', 'rejected')->get(),
            ],
        ]);
    }

    public function getCompanySubmissions() {
        $user = User::where('role', 'company')
        ->selectRaw('users.id, name, email, role, status');
        return response()->json([
            'message' => 'Berhasil memuat data.',
            'data' => [
                'panding' => $user->where('status', 'panding')->get(),
                'accepted' =>$user->where('status', 'accepted')->get(),
                'rejected' =>$user->where('status', 'rejected')->get(),
            ],
        ]);
    }

    public function setUserStatus($user_id, $status) {
        $user = User::find($user_id);
        if (!$user) {
            return response()->json([
                'message' => 'Pengguna tidak ditemukan.',
            ], 404);
        }
        if (!in_array($status, ['panding', 'accepted', 'rejected'])) {
            return response()->json([
                'message' => 'Status tidak valid.',
            ], 400);
        }
        $user->update([
            'status' => $status,
        ]);
        return response()->json([
            'message' => 'Perubahan berhasil disimpan.',
            'data' => $user->getDetail(),
        ], 200);
    }
}