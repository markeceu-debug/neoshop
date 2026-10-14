<?php

use App\Http\Controllers\User\DashboardController;
use App\Http\Controllers\User\OrderController;
use App\Http\Controllers\User\ProfileController;
 use App\Http\Controllers\User\DisputeController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:user'])
    ->prefix('minha-conta')
    ->name('user.')
    ->group(function () {

        // ---------- DASHBOARD ----------
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // ---------- PEDIDOS ----------
        Route::prefix('pedidos')->name('orders.')->group(function () {
            Route::get('/',         [OrderController::class, 'index'])->name('index');
            Route::get('/{order}',  [OrderController::class, 'show']) ->name('show');
            Route::post('/{order}/delivered', [OrderController::class, 'markDelivered'])->name('delivered');
        });

        // ---------- PERFIL ----------
        Route::prefix('perfil')->name('profile.')->group(function () {
            Route::get('/',  [ProfileController::class, 'edit'])  ->name('edit');
            Route::put('/',  [ProfileController::class, 'update'])->name('update');
        });

      
// ---------- RECLAMAÇÕES ----------
Route::prefix('reclamacoes')->name('disputes.')->group(function () {
    Route::get('/pedido/{order}/nova',  [DisputeController::class, 'create']) ->name('create');
    Route::post('/pedido/{order}',      [DisputeController::class, 'store'])  ->name('store');
    Route::get('/{dispute}',            [DisputeController::class, 'show'])   ->name('show');
    Route::post('/{dispute}/mensagem',  [DisputeController::class, 'message'])->name('message');
});
    });