<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\RolePermissionController;
use App\Http\Controllers\API\DashboardController;
use App\Http\Controllers\API\NewsController;
use App\Http\Controllers\API\TransactionController;
use App\Http\Controllers\API\CashRegisterController; 
use App\Http\Controllers\API\ProfileController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// 🟢 RUTAS PÚBLICAS
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login',    [AuthController::class, 'login']);
Route::post('/password/forgot', [AuthController::class, 'sendResetLinkEmail']);
Route::post('/password/reset',  [AuthController::class, 'resetPassword']);

// 🔐 RUTAS PROTEGIDAS (JWT)
Route::middleware('auth:api')->group(function () {

    // PERFIL
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // ROLES Y PERMISOS
    Route::get('roles-permissions', [RolePermissionController::class, 'index']);
    Route::get('roles/{id}/permissions', [RolePermissionController::class, 'permissions']);
    Route::put('roles/{id}/permissions', [RolePermissionController::class, 'update']);
    Route::post('roles/{id}/permissions/assign', [RolePermissionController::class, 'assignPermission']);
    Route::post('roles/{id}/permissions/revoke', [RolePermissionController::class, 'revokePermission']);

    // DASHBOARD ADMIN
    Route::prefix('admin/dashboard')->group(function () {
        Route::get('/stats',  [DashboardController::class, 'getStats']);
        Route::get('/charts', [DashboardController::class, 'getCharts']);

        Route::get('/users', [DashboardController::class, 'indexUsers']);
        Route::post('/users', [DashboardController::class, 'storeUser']);
        Route::put('/users/{id}', [DashboardController::class, 'updateUser']);
        Route::delete('/users/{id}', [DashboardController::class, 'destroyUser']);
    });

    // NEWS CRUD
    Route::apiResource('news', NewsController::class);

    // TRANSACTIONS
    Route::prefix('transactions')->group(function () {
        Route::get('/', [TransactionController::class, 'index']);
        Route::get('/day', [TransactionController::class, 'day']);
        Route::post('/', [TransactionController::class, 'store']);
    });

    // CASH REGISTER
    Route::prefix('cash-register')->group(function () {
        Route::post('/open', [CashRegisterController::class, 'open']);
        Route::post('/close', [CashRegisterController::class, 'close']);
        Route::get('/today', [CashRegisterController::class, 'today']);
        Route::get('/closures', [CashRegisterController::class, 'closures']);
        Route::get('/summary/{date}', [CashRegisterController::class, 'summaryDetail']);
    });

    //PROFILE

        Route::get('/profile', [ProfileController::class, 'show']);
    Route::put('/profile', [ProfileController::class, 'update']);
    Route::post('/profile/avatar', [ProfileController::class, 'updateAvatar']);

    // DASHBOARD FULL
    Route::get('/dashboard', [DashboardController::class, 'index']);
});

Route::get('/ping', function () {
    return response()->json(['ok' => true]);
});