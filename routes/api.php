<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CategoriesController;

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes (butuh token)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    
    // Contoh route yang dilindungi
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    
   //Route Product
   Route::apiResource('products', ProductController::class);

   //Route Categories
   Route::apiResource('categories', CategoriesController::class);


});
