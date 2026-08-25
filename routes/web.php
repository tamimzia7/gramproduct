<?php

use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\ProductImageController as AdminProductImageController;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\WishlistController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'home'])->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);

    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store']);

    Route::get('/forgot-password', [ForgotPasswordController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'store'])->name('password.email');

    Route::get('/reset-password/{token}', [ResetPasswordController::class, 'create'])->name('password.reset');
    Route::post('/reset-password', [ResetPasswordController::class, 'store'])->name('password.update');
});

Route::post('/logout', LogoutController::class)->name('logout')->middleware('auth');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [HomeController::class, 'dashboard'])->name('dashboard');

    Route::get('/email/verify', [EmailVerificationController::class, 'notice'])
        ->name('verification.notice');

    Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
        ->name('verification.verify')
        ->middleware('signed');

    Route::post('/email/verification-notification', [EmailVerificationController::class, 'resend'])
        ->name('verification.send')
        ->middleware('throttle:6,1');

    Route::prefix('wishlist')->name('wishlist.')->group(function () {
        Route::get('/', [WishlistController::class, 'index'])->name('index');
        Route::post('/', [WishlistController::class, 'store'])->name('store');
        Route::delete('/{wishlistItem}', [WishlistController::class, 'destroy'])->name('destroy');
        Route::post('/{wishlistItem}/move-to-cart', [WishlistController::class, 'moveToCart'])->name('move-to-cart');
    });
});

Route::middleware(['auth', 'can:admin.access'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [HomeController::class, 'adminDashboard'])->name('dashboard');

    Route::resource('categories', AdminCategoryController::class)->except(['show']);
    Route::get('categories/{category}', [AdminCategoryController::class, 'show'])->name('categories.show');
    Route::patch('categories/{id}/restore', [AdminCategoryController::class, 'restore'])->name('categories.restore');

    Route::resource('products', AdminProductController::class);
    Route::patch('products/{product}/images/{image}/primary', [AdminProductImageController::class, 'makePrimary'])
        ->name('products.images.primary');
    Route::delete('products/{product}/images/{image}', [AdminProductImageController::class, 'destroy'])
        ->name('products.images.destroy');
});

Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/{product:slug}', [ProductController::class, 'show'])->name('products.show');

Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
Route::get('/categories/{category:slug}', [CategoryController::class, 'show'])->name('categories.show');
