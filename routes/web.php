<?php

use App\Http\Controllers\CartController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\CouponController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\TrackingController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| LOJA (público)
|--------------------------------------------------------------------------
*/

Route::get('/',                          [ShopController::class, 'home'])->name('home');
Route::get('/produtos',                  [ShopController::class, 'index'])->name('shop.index');
Route::get('/produto/{product}',         [ShopController::class, 'show'])->name('product.show');
Route::get('/categoria/{category:slug}', [ShopController::class, 'category'])->name('shop.category');

/*
|--------------------------------------------------------------------------
| CARRINHO (público — cliente anônimo também)
|--------------------------------------------------------------------------
*/
Route::prefix('cart')->name('cart.')->group(function () {
    Route::get('/',          [CartController::class, 'index'])->name('index');
    Route::get('/mini',      [CartController::class, 'mini'])->name('mini');
    Route::post('/add',      [CartController::class, 'add'])->name('add');
    Route::put('/{item}',    [CartController::class, 'update'])->name('update');
    Route::delete('/{item}', [CartController::class, 'remove'])->name('remove');
    Route::delete('/',       [CartController::class, 'clear'])->name('clear');
    Route::post('/sync',     [CartController::class, 'sync'])->name('sync');
});

/*
|--------------------------------------------------------------------------
| CUPOM (público)
|--------------------------------------------------------------------------
*/
Route::post('/coupon/apply',    [CouponController::class, 'apply'])->name('coupon.apply');
Route::delete('/coupon/remove', [CouponController::class, 'remove'])->name('coupon.remove');

/*
|--------------------------------------------------------------------------
| CHECKOUT (público — cliente anônimo também)
|--------------------------------------------------------------------------
*/
Route::prefix('checkout')->name('checkout.')->group(function () {
    Route::get('/',                     [CheckoutController::class, 'index'])->name('index');
    Route::post('/',                    [CheckoutController::class, 'store'])->name('store');
    Route::post('/calculate-shipping',  [CheckoutController::class, 'calculateShipping'])->name('shipping');
});

/*
|--------------------------------------------------------------------------
| PEDIDO (público)
|--------------------------------------------------------------------------
*/
Route::get('/pedido/{order}',               [CheckoutController::class, 'success'])->name('checkout.success');
Route::get('/pedido/{order}/pagamento',     [CheckoutController::class, 'payment'])->name('checkout.payment');
Route::post('/pedido/{order}/comprovativo', [CheckoutController::class, 'uploadProof'])->name('checkout.uploadProof');

/*
|--------------------------------------------------------------------------
| CONTATO
|--------------------------------------------------------------------------
*/
Route::get('/contato', fn() => view('shop.contact'))->name('shop.contact');

/*
|--------------------------------------------------------------------------
| PERFIL (autenticado)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/profile',    [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile',  [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::get('/escolher-municipio', function () {
    session()->forget(['customer_province', 'customer_municipality']);
    return redirect()->route('home');
})->name('shop.clear-municipality');




Route::get('/rastreio',              [TrackingController::class, 'index'])->name('tracking.index');
Route::get('/rastreio/{number}',     [TrackingController::class, 'show'])->name('tracking.show');
require __DIR__ . '/auth.php';
