<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Mail\CustomVerifyEmail;
use App\Models\CompanyDetail;
use App\Models\LectureDetail;
use App\Models\StudentDetail;
use App\Models\User;
use App\Models\UserSubmission;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Http\Request;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class AuthenticationController extends Controller
{
    public function companyRegister(Request $request) {
        try {
            $val = Validator::make($request->all(), [
                'name' => 'required',
                'role' => 'required',
                'email' => 'required|email|unique:users,email',
                'password' => 'required',
                'description' => 'required',
                'address' => 'required',
                'phone' => 'required|numeric',
            ]);
            if ($val->fails()) {
                return response()->json([
                    'message' => 'Bidang tidak valid',
                    'data' => $val->errors()
                ], 400);
            }
            $user = User::create([
                'name' => trim(strtolower($request->name)),
                'password' => Hash::make($request->password),
                'email' => trim(strtolower($request->email)),
                'role' => trim(strtolower($request->role))
            ]);
            CompanyDetail::create([
                'user_id' => $user->id,
                'description' => trim($request->description),
                'address' => trim($request->address),
                'phone' => trim($request->phone),
            ]);
            $this->sendEmailVerification($user);
            return response()->json([
                'status' => true,
                'message' => 'Registrasi berhasil, Silakan periksa email Anda untuk melakukan verifikasi email.',
                'data' => $user->getDetail(),
            ], 200);

        } catch (\Throwable $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    public function lectureRegister(Request $request) {
        try {
            $val = Validator::make($request->all(), [
                'name' => 'required',
                'role' => 'required',
                'email' => 'required|email|unique:users,email',
                'password' => 'required',
                'nidn' => 'required|numeric',
                'address' => 'required',
                'phone' => 'required|numeric',
                'college_id' => 'required|exists:users,id',
            ]);
            if ($val->fails()) {
                return response()->json([
                    'message' => 'Bidang tidak valid',
                    'data' => $val->errors()
                ], 400);
            }
            $user = User::create([
                'name' => trim(strtolower($request->name)),
                'password' => Hash::make($request->password),
                'email' => trim(strtolower($request->email)),
                'role' => trim(strtolower($request->role)),
            ]);
            LectureDetail::create([
                'user_id' => $user->id,
                'nidn' => trim($request->nidn),
                'address' => trim($request->address),
                'phone' => trim($request->phone),
                'college_id' => trim($request->college_id),
            ]);
            $this->sendEmailVerification($user);
            return response()->json([
                'message' => 'Registrasi berhasil, silahkan login.',
                'data' => $user->getDetail(),
            ], 200);

        } catch (\Throwable $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    public function studentRegister(Request $request) {
        try {
            $val = Validator::make($request->all(), [
                'name' => 'required',
                'role' => 'required',
                'email' => 'required|email|unique:users,email',
                'nim' => 'required|numeric',
                'password' => 'required',
                'address' => 'required',
                'phone' => 'required|numeric',
                'college_id' => 'required|exists:users,id',
            ]);
            if ($val->fails()) {
                return response()->json([
                    'message' => 'Bidang tidak valid',
                    'data' => $val->errors()
                ], 400);
            }
            $user = User::create([
                'name' => trim(strtolower($request->name)),
                'password' => Hash::make($request->password),
                'email' => trim(strtolower($request->email)),
                'role' => trim(strtolower($request->role)),
            ]);
            StudentDetail::create([
                'user_id' => $user->id,
                'nim' => trim($request->nim),
                'address' => trim($request->address),
                'phone' => trim($request->phone),
                'college_id' => trim($request->college_id),
            ]);
            $this->sendEmailVerification($user);
            return response()->json([
                'message' => 'Registrasi berhasil, silahkan login.',
                'data' => $user->getDetail(),
            ], 200);

        } catch (\Throwable $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    public function login(Request $request) {
        try {
            $val = Validator::make($request->all(), [
                'email' => 'required',
                'password' => 'required'
            ]);
            if ($val->fails()) {
                return response()->json([
                    'message' => 'Bidang tidak valid',
                    'data' => $val->errors()
                ], 400);
            }
            if (!Auth::attempt($request->only(['email', 'password']))) {
                return response()->json([
                    'message' => 'Login gagal. Silakan periksa kembali informasi login Anda.',
                    'data' => null,
                ], 401);
            }
            $user = User::where('email', $request->email)->first();

            // Verifikasi Email Validate (Non Active For While)
            if (!$user->hasVerifiedEmail()) {
                return response()->json([
                    'message' => 'Gagal login. Silakan verifikasi email Anda untuk melanjutkan.',
                    'data' => null,
                ], 403);
            }
            return response()->json([
                'message' => 'Anda telah berhasil masuk ke akun Anda.',
                'data' => [
                    'access_token' => $user->createToken(time())->plainTextToken,
                    'user' => $user->getDetail(),
                ]
            ], 200);

        } catch (\Throwable $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    public function me() {
        $user = User::find(Auth::user()->id);
        return response()->json([
            'message' => 'Anda telah berhasil masuk ke akun Anda.',
            'data' => $user->getDetail(),
        ], 200);
    }

    public function logout() {
        $user = Auth::user();
        $user->tokens()->delete();
    
        return response()->json([
            'message' => 'Logout berhasil',
            'data' => null,
        ], 200);
    }

    public function sendEmailVerification(User $user) {
        Mail::to($user)->send(new CustomVerifyEmail($user));
    }
}
