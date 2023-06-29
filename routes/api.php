<?php

use App\Http\Controllers\api\AuthenticationController;
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
    Route::post('login', [AuthenticationController::class, 'login'])->name('auth.login');
    Route::get('me', [AuthenticationController::class, 'me'])->name('auth.me')->middleware(['auth:sanctum']);
    Route::post('logout', [AuthenticationController::class, 'logout'])->name('auth.logout')->middleware(['auth:sanctum']);
});
