<?php

use App\Http\Controllers\Api\PredictController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Cache;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\Api\ResultController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
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
// 인증 요청 메일 보내기
Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();
    return response()->json(['message' => '이메일 인증 메일을 보냈어요.']);
})->middleware(['auth:sanctum']);
Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    Route::get('/results', [resultController::class, 'index']);
    Route::get('/results/{id}', [ResultController::class, 'show']);
    Route::delete('/results/{id}', [ResultController::class, 'destroy']);
});
// 인증 링크 클릭했을 때
Route::get('/verify-email/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill(); // 인증 완료 처리
    return redirect('/verified-success'); // 프론트 페이지로 리디렉션

})->middleware(['signed'])->name('verification.verify');
Route::prefix('predict')->group(function () {
    Route::get('/', [PredictController::class, 'index']);
    Route::post('/', [PredictController::class, 'store']);
    Route::post('/{id}/opinion', [PredictController::class, 'opinionStore']);
});

Route::get('/disease-info', [SearchController::class, 'infoApi']);
Route::get('/diseases', [SearchController::class, 'diseaseListApi']);
Route::get('/cache-test', function () {
    Cache::put('greeting', '안녕, 라라독 Redis!', now()->addMinutes(10));
    return [
        'driver' => config('cache.default'),
        'value' => Cache::get('greeting'),
    ];
});




Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
