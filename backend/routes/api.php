<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Cache;
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
Route::get('/cache-test', function () {
    Cache::put('greeting', '안녕, 라라독 Redis!', 10);

    return Cache::get('greeting');
});
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
