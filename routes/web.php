<?php

// ====================================================================
// routes/web.php — TR 11
// REQ 8: route protection:
//   publik          -> katalog toko
//   auth            -> dashboard, profil, pesanan saya (semua role)
//   role:admin,editor -> kelola produk
//   role:admin      -> kelola pengguna
// ====================================================================

use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// ---- Publik ----
Route::get('/', [ShopController::class, 'index'])->name('shop.index');
Route::get('/produk/{product:slug}', [ShopController::class, 'show'])->name('shop.show');

// ---- Wajib login (semua role) ----
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    // Rute profil bawaan Breeze (dipertahankan)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Beli langsung (satu produk per pesanan)
    Route::get('/produk/{product:slug}/beli', [CheckoutController::class, 'create'])->name('checkout.create');
    Route::post('/produk/{product:slug}/beli', [CheckoutController::class, 'store'])->name('checkout.store');

    Route::get('/pesanan', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/pesanan/{order}', [OrderController::class, 'show'])->name('orders.show');
});

// ---- Admin & editor: kelola produk (editor dibatasi ProductPolicy) ----
Route::middleware(['auth', 'role:admin,editor'])
    ->prefix('kelola')->name('manage.')
    ->group(function () {
        Route::resource('products', ProductController::class)->except('show');
    });

// ---- Admin saja: kelola role pengguna ----
Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')->name('admin.')
    ->group(function () {
        Route::get('users', [UserController::class, 'index'])->name('users.index');
        Route::patch('users/{user}/role', [UserController::class, 'updateRole'])->name('users.role');
    });

// Rute login / register / logout / reset password dari Breeze
require __DIR__.'/auth.php';
