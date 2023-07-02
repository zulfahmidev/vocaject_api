<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index($role) {
        $users = User::where('role', strtolower($role))->get();
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
}
