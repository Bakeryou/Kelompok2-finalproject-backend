<?php

use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Route untuk login
Route::post('login', [AuthController::class, 'login']);

// Route untuk register
Route::post('register', [AuthController::class, 'register']);

// Route-rute yang memerlukan autentikasi JWT
Route::middleware('auth:api')->group(function () {
    // Route untuk mendapatkan informasi pengguna yang sedang login
    Route::get('me', [AuthController::class, 'me']);

    // Route untuk logout
    Route::post('logout', [AuthController::class, 'logout']);

    // Route untuk update profile
    Route::post('update-profile', [AuthController::class, 'updateProfile']);

    // Rute khusus untuk admin
    Route::middleware(['auth:api', 'checkrole:admin'])->group(function () {
        Route::get('admin/dashboard', [AdminController::class, 'dashboard']);
        
        // Route untuk menambahkan produk
        Route::post('products', [ProductController::class, 'store']);
        Route::get('products/{id}', [ProductController::class, 'show']);
        Route::get('products', [ProductController::class, 'index']);
        // Route untuk mengedit produk
        Route::post('products/{id}', [ProductController::class, 'update']);
        // Route untuk menghapus produk
        Route::delete('products/{id}', [ProductController::class, 'destroy']);

        // Route untuk menambahkan kategori
        Route::post('categories', [CategoryController::class, 'store']);
        Route::get('categories/{id}', [CategoryController::class, 'show']);
        Route::get('categories', [CategoryController::class, 'index']);
        // Route untuk mengedit kategori
        Route::post('categories/{id}', [CategoryController::class, 'update']);
        // Route untuk menghapus kategori
        Route::delete('categories/{id}', [CategoryController::class, 'destroy']);
    });

    // Rute khusus untuk pengguna
    Route::middleware('checkrole:user')->group(function () {
        Route::get('user/dashboard', [UserController::class, 'dashboard']);
        // Rute-rute lain untuk pengguna
    });
});
