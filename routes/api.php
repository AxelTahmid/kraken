<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\Auth\AuthController;


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

Route::get('/', function () {
    return response()->json('Hello There');
});

Route::post('/clear', function () {
    Artisan::call('optimize:clear');
    return response()->json([
        'success' => true,
        'message' => 'All Cache Purged Sucessfully',
    ], 200);
});

Route::prefix('auth')->group(function () {

    Route::post('login', [AuthController::class, 'login'])->name('login');
    Route::post('register', [AuthController::class, 'register']);

    Route::middleware('auth:api')->group(function () {

        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me', [AuthController::class, 'user']);
    });
});


Route::get('/roles', [PermissionController::class, 'Permission']);

Route::middleware('role:developer')->group(function () {

    Route::get('/admin', function () {
        return response()->json('Welcome Admin');
    });
});
