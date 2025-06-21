<?php
use App\Http\Controllers\Auth\{
    AuthController,
    VerificationController
};
use App\Http\Controllers\Api\PredictController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Cache;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\Api\ResultController;

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

// 회원가입
Route::post('/register', [AuthController::class, 'register']);
Route::post('/notification', [VerificationController::class, 'notification']);
// 인증번호 확인
Route::get('/check-email', [VerificationController::class, 'check_mail']);
Route::post('/verify', [VerificationController::class, 'verify']);
// 인증번호 재발송
Route::post('/resend-code', [VerificationController::class, 'resend']);

Route::get('/results', [resultController::class, 'index']);
Route::get('/results/{id}', [ResultController::class, 'show']);
Route::delete('/results/{id}', [ResultController::class, 'destroy']);

Route::prefix('predict')->middleware('auth:api')->group(function () {
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
