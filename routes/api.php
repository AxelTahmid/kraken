<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\RoleController;
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

Route::prefix('/auth')->controller(AuthController::class)->group(function () {

    Route::post('/login', 'login')->name('login');
    Route::post('/register', 'register');

    Route::middleware('auth:api')->group(function () {
        Route::post('/logout', 'logout');
        Route::get('/me', 'user');
    });
});



Route::prefix('/admin')->middleware('auth:api')->group(function () {

    Route::middleware('role:admin')->group(function () {

        Route::get('/dashboard', function () {
            return response()->json('Welcome Admin');
        });
        Route::apiResource('/role', RoleController::class);
        Route::apiResource('/permission', PermissionController::class);
    });
});
