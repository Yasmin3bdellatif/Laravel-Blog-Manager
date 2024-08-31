<?php

use App\Http\Controllers\StatsController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TagController;
use App\Http\Controllers\PostController;



//Route::get('/user', function (Request $request) {
//    return $request->user();
//})->middleware('auth:sanctum');
//

#region log
Route::prefix('auth')->group(function () {
    Route::post('/register', [UserController::class, 'register']);
    Route::post('/login', [UserController::class, 'login']);
    Route::post('/verify-code', [UserController::class, 'verifyCode']);
});
#endregion

#region tag
Route::middleware('auth:sanctum')->resource('tags', TagController::class);
#endregion

#region posts
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('posts', PostController::class);
    Route::get('post/trashed', [PostController::class, 'trashed']);
    Route::post('post/  ', [PostController::class, 'restore']);
});
#endregion

Route::get('/stats', [StatsController::class, 'index']);

