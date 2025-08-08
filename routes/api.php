<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CategoriesController; // Pastikan nama controller ini benar
use App\Http\Controllers\Api\OrderController;

/*
|--------------------------------------------------------------------------
| Rute Publik
|--------------------------------------------------------------------------
|
| Endpoint yang bisa diakses oleh siapa saja, tanpa perlu login.
|
*/
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Rute untuk melihat produk dan kategori bersifat publik
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{product}', [ProductController::class, 'show']);
Route::get('/categories', [CategoriesController::class, 'index']);
Route::get('/categories/{category}', [CategoriesController::class, 'show']);
Route::get('/products/category/{slug}', [ProductController::class, 'getByCategorySlug']);


/*
|--------------------------------------------------------------------------
| Rute Terproteksi (Harus Login)
|--------------------------------------------------------------------------
|
| Endpoint di dalam grup ini memerlukan token otentikasi (Sanctum).
|
*/
Route::middleware('auth:sanctum')->group(function () {
    
    // --- Rute untuk SEMUA PERAN yang sudah login ---
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // --- Rute KHUSUS BUYER ---
    Route::middleware('can:is-buyer')->group(function() {
        Route::post('/orders', [OrderController::class, 'store']);
        Route::get('/my-orders', [OrderController::class, 'myOrders']); // Asumsi method ini ada
    });

    // --- Rute KHUSUS ADMIN ---
    Route::middleware('can:is-admin')->group(function() {
        // Mengelola Produk (Hanya Admin)
        // Menggunakan except() untuk mengeluarkan method yang sudah didefinisikan di publik
        Route::apiResource('products', ProductController::class)->except(['index', 'show']);

        // Mengelola Kategori (Hanya Admin)
        Route::apiResource('categories', CategoryController::class)->except(['index', 'show']);
        
        // Mengelola Semua Pesanan (Hanya Admin)
        Route::get('/orders', [OrderController::class, 'index']); // Admin melihat semua pesanan
        Route::get('/orders/{order}', [OrderController::class, 'show']); // Admin melihat detail pesanan
        // Route::patch('/orders/{order}/status', [OrderController::class, 'updateStatus']); // Contoh untuk update status
    });
});