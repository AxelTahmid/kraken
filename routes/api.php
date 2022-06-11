<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\RBAC\PermissionAccessController;
use App\Http\Controllers\RBAC\RolePermissionController;
use App\Http\Controllers\RBAC\UserRoleController;

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



Route::prefix('/admin')->middleware('auth:api', 'role:admin')->group(function () {

    Route::get('/dashboard', function () {
        return response()->json('Welcome Admin');
    });

    Route::prefix('/permission')->controller(PermissionController::class)->group(function () {
        Route::get('/', 'index')->middleware('can:read-permission');
        Route::post('/', 'store')->middleware('can:create-permission');
        Route::get('/{slug}', 'show')->middleware('can:read-permission');
        Route::patch('/{slug}', 'update')->middleware('can:update-permission');
        Route::delete('/{slug}', 'destroy')->middleware('can:delete-permission');
    });

    Route::prefix('/role')->controller(RoleController::class)->group(function () {
        Route::get('/', 'index')->middleware('can:read-role');
        Route::post('/', 'store')->middleware('can:create-role');
        Route::get('/{slug}', 'show')->middleware('can:read-role');
        Route::patch('/{slug}', 'update')->middleware('can:update-role');
        Route::delete('/{slug}', 'destroy')->middleware('can:delete-role');
    });

    Route::prefix('/access-control')->group(function () {
        Route::post('/user-role', UserRoleController::class)->middleware('can:manage-role');
        Route::post('/role-permissions', RolePermissionController::class)->middleware('can:manage-permission');
        Route::post('/user-permissions', [PermissionAccessController::class, 'manageUserPermissions'])->middleware('can:manage-permission');
    });

    Route::apiResource('/user', UserController::class)->only(['index', 'show']);
});
