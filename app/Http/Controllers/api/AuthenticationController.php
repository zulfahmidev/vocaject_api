<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Mail\CustomVerifyEmail;
use App\Mail\ResetPasswordEmail;
use App\Models\CompanyDetail;
use App\Models\LectureDetail;
use App\Models\PasswordReset;
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
                'name' => 'required|min:3',
                'role' => 'required',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|min:8',
                'description' => 'required|min:3',
                'address' => 'required|min:3',
                'phone' => 'required|numeric|min:3',
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
                'name' => 'required|min:3',
                'role' => 'required',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|min:8',
                'nidn' => 'required|numeric|min:3',
                'address' => 'required|min:3',
                'phone' => 'required|numeric|min:3',
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
                'nim' => 'required|numeric|min:3',
                'password' => 'required|min:8',
                'address' => 'required|min:3',
                'phone' => 'required|numeric|min:3',
                'college_id' => 'required|exists:users,id',
            ]);
            if ($val->fails()) {
                return response()->json([
                    'message' => 'Bidang tidak valid',
                    'data' => $val->errors()
                ], 400);
            }
            $college = User::find($request->college_id);
            if ($college) {
                if ($college->role != 'college') {
                    return response()->json([
                        'message' => 'Id yang anda inputkan bukan kampus.',
                        'data' => $val->errors()
                    ], 400);
                }
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
            // if (!$user->hasVerifiedEmail()) {
            //     return response()->json([
            //         'message' => 'Gagal login. Silakan verifikasi email Anda untuk melanjutkan.',
            //         'data' => null,
            //     ], 403);
            // }

            if ($request->platform == 'web') {
                if (!in_array($user->role, ['company', 'college'])) {
                    return response()->json([
                        'message' => 'Gagal login. Anda tidak dapat login sebagai Dosen/Mahasiswa melalui website.',
                        'data' => null,
                    ], 403);
                }
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

    public function forgotPassword(Request $request) {
        $val = Validator::make($request->all(), [
            "email" => "required|email|exists:users,email"
        ]);

        if ($val->fails()) {
            return response()->json([
                "message" => "Bidang tidak valid.",
                "data" => $val->errors(),
            ], 400);
        }

        $user = User::where('email', $request->email)->first();

        $time = time();
        $code = $this->addZero(rand(10,99)) . ' '. substr($this->addZero($user->id), -2) . ' '. substr($time, -2);
        PasswordReset::create([
            "email" => $user->email,
            "code_otp" => $code,
        ]);

        Mail::to($user)->send(new ResetPasswordEmail($user, $code));

        return response()->json([
            "message" => "Kode OTP telah dikirimkan ke email anda.",
            "data" => [
                "user" => $user->getDetail(),
                "code_otp" => $code
            ],
        ]);
    }

    private function addZero($number) {
        if ($number < 10) {
            return "0" + $number;
        }
        return $number;
    }

    public function checkOTP(Request $request) {
        $val = Validator::make($request->all(), [
            "code_otp" => "required|exists:password_resets,code_otp",
            "email" => "required|exists:password_resets,email",
        ]);

        if ($val->fails()) {
            return response()->json([
                "message" => "Bidang tidak valid.",
                "data" => $val->errors(),
            ], 400);
        }
        $pr = PasswordReset::where('email', $request->email)->where('code_otp', $request->code_otp)->first();
        if ($pr) {
            return response()->json([
                "message" => "Kode OTP valid.",
                "data" => true,
            ]);
        }
        return response()->json([
            "message" => "Kode OTP tidak valid.",
            "data" => null,
        ], 400);
    }

    public function changePassword(Request $request) {
        $val = Validator::make($request->all(), [
            "code_otp" => "required|exists:password_resets,code_otp",
            "email" => "required|exists:password_resets,email",
            "password" => "required|string|min:8",
            "confirm_password" => "required|string|same:password",
        ]);

        if ($val->fails()) {
            return response()->json([
                "message" => "Bidang tidak valid.",
                "data" => $val->errors(),
            ], 400);
        }
        $pr = PasswordReset::where('email', $request->email)->where('code_otp', $request->code_otp)->first();
        if (!$pr) {
            return response()->json([
                "message" => "Kode OTP tidak valid.",
                "data" => null,
            ], 400);
        }
        $user = User::where('email', $request->email)->first();
        $user->password = $request->password;
        $user->save();
        return response()->json([
            "message" => "Password berhasil diubah.",
            "data" => null,
        ]);

    }
}
