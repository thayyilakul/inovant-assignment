<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CartController;

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

// Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::redirect('/', '/login');

Route::redirect('/home', '/products');

Route::get('/products', [App\Http\Controllers\ProductController::class, 'index'])->name('home');
Route::get('/products/list', [App\Http\Controllers\ProductController::class, 'list'])->name('product-list');
Route::get('/products/create', [App\Http\Controllers\ProductController::class, 'create'])->name('product-create');
Route::get('/products/edit/{id}', [App\Http\Controllers\ProductController::class, 'editProduct'])->name('product-edit');

Route::get('/cart', [CartController::class, 'index'])->name('cart-page');
Route::get('/carts', [CartController::class, 'showCart']);
