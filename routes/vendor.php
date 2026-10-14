<?php

use App\Http\Controllers\Vendor\ShippingController;
use App\Http\Controllers\Vendor\ProfileController;
use App\Http\Controllers\Vendor\OrderController;
use App\Http\Controllers\Vendor\WithdrawalController;

use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:vendor'])
    ->prefix('vendor')
    ->name('vendor.')
    ->group(function () {

        // Dashboard
        Route::get('/dashboard', function () {
            return view('vendedor.dashboard');
        })->name('dashboard');

        // Perfil
        Route::prefix('profile')->name('profile.')->group(function () {
            Route::get('/',  [ProfileController::class, 'edit'])->name('edit');
            Route::put('/',  [ProfileController::class, 'update'])->name('update');
        });
        // Frete
        Route::prefix('shipping')->name('shipping.')->group(function () {
            Route::get('/',  [ShippingController::class, 'edit'])->name('edit');
            Route::put('/',  [ShippingController::class, 'update'])->name('update');
        });


        // Pedidos
        Route::prefix('orders')->name('orders.')->group(function () {
            Route::get('/',           [OrderController::class, 'index'])->name('index');
            Route::get('/{order}',    [OrderController::class, 'show'])->name('show');
            Route::post('/{order}/shipped', [OrderController::class, 'markShipped'])->name('shipped');
        });

Route::prefix('withdrawals')->name('withdrawals.')->group(function () {
    Route::get('/',        [WithdrawalController::class, 'index']) ->name('index');
    Route::get('/create',  [WithdrawalController::class, 'create'])->name('create');
    Route::post('/',       [WithdrawalController::class, 'store']) ->name('store');
});
    });
