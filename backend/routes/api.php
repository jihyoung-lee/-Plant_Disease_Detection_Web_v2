<?php

use App\Http\Controllers\Api\PredictionController;
use App\Http\Controllers\Api\PredictionResultController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\GoogleLoginController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\VerificationController;
use App\Http\Controllers\SearchController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::options('/{any}', function () {
    return response()->json([], 200);
})->where('any', '.*');

// 회원가입
Route::post('/register', [AuthController::class, 'register']);
Route::post('/notification', [VerificationController::class, 'notification']);
// 인증번호 확인
Route::get('/check-email', [VerificationController::class, 'checkEmail']);
Route::post('/verify', [VerificationController::class, 'verify']);
// 인증번호 재발송
Route::post('/resend-code', [VerificationController::class, 'resend']);
// 로그인
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->middleware('auth:api');
Route::get('/me', [LoginController::class, 'me'])->middleware('auth:api');

// Google 로그인
Route::post('/auth/google', [GoogleLoginController::class, 'handle']);

Route::middleware('auth:api')->group(function () {
    Route::get('/results', [PredictionResultController::class, 'index']);
    Route::get('/results/{id}', [PredictionResultController::class, 'show']);
    Route::delete('/results/{id}', [PredictionResultController::class, 'destroy']);

    Route::prefix('predict')->group(function () {
        Route::post('/', [PredictionController::class, 'store']);
        Route::post('/{id}/opinion', [PredictionController::class, 'storeOpinion']);
    });
});

Route::get('/disease-info', [SearchController::class, 'infoApi']);
Route::get('/diseases', [SearchController::class, 'diseaseListApi']);

Route::get('/csrf-token', function () {
    return response()->json(['csrf_token' => csrf_token()]);
})->middleware('web');
