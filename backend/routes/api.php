<?php

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

Route::get('/results', [ResultController::class, 'index']);
Route::get('/results/{id}', [ResultController::class, 'show']);
Route::delete('/results/{id}', [ResultController::class, 'destroy']);

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
