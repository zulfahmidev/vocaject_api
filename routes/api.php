<?php

use App\Http\Controllers\api\AuthenticationController;
use App\Http\Controllers\api\UserSubmissionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });
Route::prefix('auth')->group(function() {
    Route::post('register/company', [AuthenticationController::class, 'companyRegister'])->name('auth.register.company');
    Route::post('register/college', [AuthenticationController::class, 'companyRegister'])->name('auth.register.company');
    Route::post('register/student', [AuthenticationController::class, 'studentRegister'])->name('auth.register.student');
    Route::post('register/lecture', [AuthenticationController::class, 'lectureRegister'])->name('auth.register.lecture');
    Route::post('login', [AuthenticationController::class, 'login'])->name('auth.login');
    Route::get('me', [AuthenticationController::class, 'me'])->name('auth.me')->middleware(['auth:sanctum']);
    Route::post('logout', [AuthenticationController::class, 'logout'])->name('auth.logout')->middleware(['auth:sanctum']);
});

Route::prefix('user')->group(function() {

    Route::prefix('submission')->group(function() {

        Route::get('student', [UserSubmissionController::class, 'getStudentSubmissions'])->name('user.submission.student');
        Route::get('lecture', [UserSubmissionController::class, 'getLectureSubmissions'])->name('user.submission.lecture');
        Route::get('college', [UserSubmissionController::class, 'getCollegeSubmissions'])->name('user.submission.college');
        Route::get('company', [UserSubmissionController::class, 'getCompanySubmissions'])->name('user.submission.company');

    });

});