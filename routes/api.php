<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CategoriesController;
use App\Http\Controllers\Api\OrderController;

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes (butuh token)
Route::middleware(['auth:sanctum', 'can:is-admin'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    //   Route::post('/orders', [OrderController::class, 'store']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::apiResource('orders', OrderController::class);
    
   //Route Product
   Route::apiResource('products', ProductController::class);

   //Route Categories
   Route::apiResource('categories', CategoriesController::class);


});
