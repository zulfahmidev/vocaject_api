<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
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

    // public function updateStudent(Request $request) {
    //     $val = Validator::make($request->all(), [
    //         'name' => 'required',
    //         'description' => 'required',
    //         'address' => 'required',
    //         'phone' => 'required|numeric',
    //         'picture' => 'required|image',
    //     ]);
    //     if ($val->fails()) {
    //         return response()->json([
    //             'message' => 'Bidang tidak valid.',
    //             'data' => $val->errors(),
    //         ]);
    //     }
    // }
}
