<?php

use App\Events\MyEvent;
use App\Events\TestAjaDah;
use App\Http\Controllers\api\AuthenticationController;
use App\Http\Controllers\api\DocumentController;
use App\Http\Controllers\api\ProjectLogbookController;
use App\Http\Controllers\api\ProjectCategoryController;
use App\Http\Controllers\api\ProjectController;
use App\Http\Controllers\api\ProjectMessageController;
use App\Http\Controllers\api\UserController;
use App\Http\Controllers\api\UserSubmissionController;
use App\Http\Controllers\api\ProposalController;
use App\Http\Controllers\api\ProjectTaskController;
use App\Models\Project;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

    Route::post('forgot-password', [AuthenticationController::class, 'forgotPassword'])->name('auth.forgot_password');
    Route::post('check-otp', [AuthenticationController::class, 'checkOTP'])->name('auth.check_otp');
    Route::post('change-password', [AuthenticationController::class, 'changePassword'])->name('auth.change_password');

    Route::get('/email/verify/{id}', function ($id) {
        $user = User::find($id);
        if (!$user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
            return response()->json([
                'message' => 'Berhasil melakukan verifikasi email.'
            ]);
        }
    })->name('auth.email.verify');
});

Route::prefix('user')->group(function() {

    Route::prefix('submission')->group(function() {

        Route::get('student', [UserSubmissionController::class, 'getStudentSubmissions'])->name('user.submission.student');
        Route::get('lecture', [UserSubmissionController::class, 'getLectureSubmissions'])->name('user.submission.lecture');

        Route::post('set/{id}/status/{status}', [UserSubmissionController::class, 'setUserStatus'])->name('user.submission.set.status');

    });

    Route::prefix('balance')->group(function() {
        Route::post('/', [UserController::class, 'topUp'])->middleware(['auth:sanctum']);
    });

    Route::get('/r/{role}', [UserController::class, 'index'])->name('user.index.role');
    Route::get('/{id}', [UserController::class, 'show'])->name('user.show');
    Route::get('/student/{college_id}', [UserController::class, 'getStudents'])->name('user.student');
    Route::get('/lecture/{college_id}', [UserController::class, 'getLectures'])->name('user.lecture');
    Route::post('/update', [UserController::class, 'updateProfile'])->name('user.update.profile')->middleware(['auth:sanctum']);

});

Route::prefix('project')->group(function() {

    Route::get('/', [ProjectController::class, 'index'])->name('project');
    Route::post('/', [ProjectController::class, 'store'])->name('project.store');
    Route::post('/{id}/update', [ProjectController::class, 'update'])->name('project.update');
    Route::delete('/{id}', [ProjectController::class, 'destroy'])->name('project.delete');
    Route::get('/{id}', [ProjectController::class, 'show'])->name('project.show');

    Route::get('/{id}/proposal_accepted', [ProposalController::class, 'getProposalAccepted'])->name('project.proposal_accepted');

    Route::prefix('category')->group(function() {
        Route::get('/all', [ProjectCategoryController::class, 'index'])->name('project.category');
        Route::post('/', [ProjectCategoryController::class, 'store'])->name('project.category.store');
        Route::post('/{id}', [ProjectCategoryController::class, 'update'])->name('project.category.update');
        Route::delete('/{id}', [ProjectCategoryController::class, 'destroy'])->name('project.category.delete');
    });

    Route::prefix('{project_id}/budget')->group(function() {
        Route::post('/', [ProjectController::class, 'manageBudget']);
    });

    Route::prefix('{project_id}/proposal')->group(function() {
        Route::get('/', [ProposalController::class, 'index'])->name('project.proposal');
        Route::get('/{proposal_id}', [ProposalController::class, 'show'])->name('project.proposal.show');
        Route::get('/by_lecture/{lecture_id}', [ProposalController::class, 'showByLecture'])->name('project.proposal.show_by_lecture');
        Route::post('/', [ProposalController::class, 'store'])->name('project.proposal.store');
        Route::post('/{proposal_id}', [ProposalController::class, 'confirm'])->name('project.proposal.confirm');
    });

    Route::prefix('{project_id}/task')->group(function() {
        Route::get('/', [ProjectTaskController::class, 'index'])->name('project.task');
        Route::post('/', [ProjectTaskController::class, 'store'])->name('project.task.store');
        Route::post('/{task_id}', [ProjectTaskController::class, 'update'])->name('project.task.update');
        Route::delete('/{task_id}', [ProjectTaskController::class, 'destroy'])->name('project.task.delete');
        Route::post('/{task_id}/switch', [ProjectTaskController::class, 'switch'])->name('project.task.switch');
    });

    Route::prefix('{project_id}/logbook/{student_id}')->group(function() {
        Route::get('/', [ProjectLogbookController::class, 'index'])->name('project.logbook');
        Route::post('/', [ProjectLogbookController::class, 'store'])->name('project.logbook.store');
    });

    Route::get('/message/get/company/contact/{project_id}', [ProjectMessageController::class, 'getCompanyContacts'])->name('project.contact.company');
    Route::get('/message/get/lecture/contact/{lecture_id}', [ProjectMessageController::class, 'getLectureContacts'])->name('project.contact.lecture');

    Route::prefix('{project_id}/message/{lecture_id}')->group(function() {
        Route::get('/', [ProjectMessageController::class, 'index'])->name('project.message');
        Route::post('/', [ProjectMessageController::class, 'store'])->name('project.store');
        Route::post('/document', [ProjectMessageController::class, 'storeDocument'])->name('project.storeDocument');
        Route::delete('/{message_id}', [ProjectMessageController::class, 'destroy'])->name('project.delete');
        Route::post('/read', [ProjectMessageController::class, 'read'])->name('project.read');
        Route::get('/count-unread', [ProjectMessageController::class, 'getCountUnread'])->name('project.count_unread');
    });
});

Route::prefix('document')->group(function(){

    Route::get('view/{filename}', [DocumentController::class, 'view'])->middleware(['auth:sanctum']);
    Route::get('detail/{filename}', [DocumentController::class, 'detail'])->middleware(['auth:sanctum']);
    // Route::post('upload', [DocumentController::class, 'upload']);
});
