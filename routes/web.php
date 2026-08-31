<?php

use App\Http\Controllers\AddressController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\InventoryController as AdminInventoryController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\ProductImageController as AdminProductImageController;
use App\Http\Controllers\Admin\ProductVariantController as AdminProductVariantController;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CheckoutController;
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

// ---------- ইচ্ছেতালিকা ----------
// store/destroyByProduct বাইরে — guest-ও কল করতে পারে; controller 401 + বাংলা নির্দেশনা দেয়
Route::post('/wishlist', [WishlistController::class, 'store'])->name('wishlist.store');
Route::delete('/wishlist/products/{product}', [WishlistController::class, 'destroyByProduct'])
    ->name('wishlist.destroy-by-product');

// ---------- কার্ট (guest + auth উভয়ের জন্য) ----------
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart', [CartController::class, 'store'])->name('cart.store');

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

    Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist.index');
    Route::delete('/wishlist/{wishlistItem}', [WishlistController::class, 'destroy'])->name('wishlist.destroy');
    Route::post('/wishlist/{wishlistItem}/move-to-cart', [WishlistController::class, 'moveToCart'])
        ->name('wishlist.move-to-cart');

    // ---------- ঠিকানা ব্যবস্থাপনা ----------
    Route::post('/addresses', [AddressController::class, 'store'])->name('addresses.store');
    Route::patch('/addresses/{address}', [AddressController::class, 'update'])->name('addresses.update');
    Route::delete('/addresses/{address}', [AddressController::class, 'destroy'])->name('addresses.destroy');
    Route::patch('/addresses/{address}/default', [AddressController::class, 'setDefault'])
        ->name('addresses.default');

    // ---------- চেকআউট ----------
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
    Route::get('/checkout/success/{order}', [CheckoutController::class, 'success'])->name('checkout.success');

    // নিজের cart item পরিবর্তন — ownership controller-এ যাচাই হয়
    Route::patch('/cart/{cartItem}', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/cart/{cartItem}', [CartController::class, 'destroy'])->name('cart.destroy');
    Route::delete('/cart', [CartController::class, 'clear'])->name('cart.clear');
});

Route::middleware(['auth', 'can:admin.access'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::resource('categories', AdminCategoryController::class)->except(['show']);
    Route::get('categories/{category}', [AdminCategoryController::class, 'show'])->name('categories.show');
    Route::patch('categories/{id}/restore', [AdminCategoryController::class, 'restore'])->name('categories.restore');

    Route::resource('products', AdminProductController::class);
    Route::patch('products/{product}/images/{image}/primary', [AdminProductImageController::class, 'makePrimary'])
        ->name('products.images.primary');
    Route::delete('products/{product}/images/{image}', [AdminProductImageController::class, 'destroy'])
        ->name('products.images.destroy');

    Route::get('products/{product}/variants/create', [AdminProductVariantController::class, 'create'])
        ->name('products.variants.create');
    Route::post('products/{product}/variants', [AdminProductVariantController::class, 'store'])
        ->name('products.variants.store');
    Route::get('products/{product}/variants/{variant}/edit', [AdminProductVariantController::class, 'edit'])
        ->name('products.variants.edit');
    Route::put('products/{product}/variants/{variant}', [AdminProductVariantController::class, 'update'])
        ->name('products.variants.update');
    Route::delete('products/{product}/variants/{variant}', [AdminProductVariantController::class, 'destroy'])
        ->name('products.variants.destroy');
    Route::patch('products/{product}/variants/{variant}/default', [AdminProductVariantController::class, 'setDefault'])
        ->name('products.variants.default');
    Route::patch('products/{product}/variants/{variant}/toggle-active', [AdminProductVariantController::class, 'toggleActive'])
        ->name('products.variants.toggle-active');

    Route::get('inventory', [AdminInventoryController::class, 'index'])->name('inventory.index');
    Route::get('inventory/{inventory}', [AdminInventoryController::class, 'show'])->name('inventory.show');
    Route::get('inventory/{inventory}/add', [AdminInventoryController::class, 'addForm'])->name('inventory.add-form');
    Route::post('inventory/{inventory}/add', [AdminInventoryController::class, 'add'])->name('inventory.add');
    Route::get('inventory/{inventory}/adjust', [AdminInventoryController::class, 'adjustForm'])->name('inventory.adjust-form');
    Route::post('inventory/{inventory}/adjust', [AdminInventoryController::class, 'adjust'])->name('inventory.adjust');
});

Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/{product:slug}', [ProductController::class, 'show'])->name('products.show');

Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
Route::get('/categories/{category:slug}', [CategoryController::class, 'show'])->name('categories.show');
