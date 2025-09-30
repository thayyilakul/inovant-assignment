<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{CartController, ProductController};

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::apiResource('products', ProductController::class);

Route::post('/cart', [CartController::class, 'addToCart'])->name('cart.add');

Route::get('/cart', [CartController::class, 'showCart']);

Route::delete('/cart/{cart}', [CartController::class, 'deleteFromCart']);

Route::get('/cart-list', [CartController::class, 'apiCartList']);
