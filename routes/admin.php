<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ProductImageController;
use App\Http\Controllers\AdminDashboardController;
  use App\Http\Controllers\Admin\CouponController;
use App\Http\Controllers\Admin\DisputeController;
use App\Http\Controllers\Admin\PromotionController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\StockMovementController;
use App\Http\Controllers\Admin\VariantController;
use App\Http\Controllers\Admin\VariantItemController;
use App\Http\Controllers\Admin\WithdrawalController;

use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\StockBulkController;
use App\Http\Controllers\Admin\ShippingZoneController;
use Illuminate\Support\Facades\Route;


Route::middleware(['auth', 'role:admin'])
     ->prefix('admin')
     ->name('admin.')
     ->group(function () {
          // Dashboard
          Route::get('/dashboard', function () {
               return view('admin.dashboard');
          })->name('dashboard');


          // ---- PRODUTOS ----
          Route::prefix('products')->name('products.')->group(function () {
               Route::get('/', [\App\Http\Controllers\Admin\ProductController::class, 'index'])->name('index');
               Route::get('/create',   [\App\Http\Controllers\Admin\ProductController::class, 'create'])->name('create');
               Route::post('/', [\App\Http\Controllers\Admin\ProductController::class, 'store'])->name('store');
               Route::get('/{product}/edit',   [\App\Http\Controllers\Admin\ProductController::class, 'edit'])->name('edit');
               Route::put('/{product}', [\App\Http\Controllers\Admin\ProductController::class, 'update'])->name('update');
               Route::delete('/{product}', [\App\Http\Controllers\Admin\ProductController::class, 'destroy'])->name('destroy');
          });

          Route::get('products/{product}/variants-json', function (\App\Models\Product $product) {
               $product->load('variants.items');
               return response()->json([
                    'product'  => ['id' => $product->id, 'name' => $product->name],
                    'variants' => $product->variants->map(function ($v) {
                         return [
                              'id'   => $v->id,
                              'name' => $v->name,
                              'type' => $v->type_label,
                              'required' => $v->required,
                              'items' => $v->items->map(function ($i) {
                                   return [
                                        'id' => $i->id,
                                        'value' => $i->value,
                                        'color_hex' => $i->color_hex,
                                        'price' => number_format($i->price ?? 0, 2, ',', '.'),
                                        'stock' => $i->stock,
                                        'image' => $i->image_url,
                                   ];
                              }),
                         ];
                    }),
               ]);
          })->name('products.variants.json');

          // ---- CATEGORIAS ----
          Route::prefix('categories')->name('categories.')->group(function () {
               Route::get('/', [CategoryController::class, 'index'])->name('index');
               Route::get('/create',   [CategoryController::class, 'create'])->name('create');
               Route::post('/', [CategoryController::class, 'store'])->name('store');
               Route::get('/{category}/edit',  [CategoryController::class, 'edit'])->name('edit');
               Route::put('/{category}',   [CategoryController::class, 'update'])->name('update');
               Route::delete('/{category}', [CategoryController::class, 'destroy'])->name('destroy');
          });



          // ---- PERFIL ----
          Route::prefix('profile')->name('profile.')->group(function () {
               Route::get('/',   [ProfileController::class, 'show'])->name('show');
               Route::get('/edit',   [ProfileController::class, 'edit'])->name('edit');
               Route::put('/',   [ProfileController::class, 'update'])->name('update');
               Route::put('/password',   [ProfileController::class, 'updatePassword'])->name('password');
               Route::delete('/avatar',  [ProfileController::class, 'destroyAvatar'])->name('avatar.destroy');
          });

          // ---- VARIANTES ----
          Route::prefix('products/{product}/variants')->name('products.variants.')->group(function () {
               Route::get('/', [VariantController::class, 'index'])->name('index');
               Route::post('/',   [VariantController::class, 'store'])->name('store');
          });
          Route::prefix('variants')->name('variants.')->group(function () {
               Route::put('/{variant}', [VariantController::class, 'update'])->name('update');
               Route::delete('/{variant}', [VariantController::class, 'destroy'])->name('destroy');
          });

          // ---- ITENS DA VARIANTE ----
          Route::post('variants/{variant}/items', [VariantItemController::class, 'store'])->name('variants.items.store');
          Route::put('variant-items/{item}', [VariantItemController::class, 'update'])->name('variants.items.update');
          Route::delete('variant-items/{item}',  [VariantItemController::class, 'destroy'])->name('variants.items.destroy');
          // ---- GALERIA ----
          Route::post('products/{product}/images',   [ProductImageController::class, 'store'])->name('products.images.store');
          Route::put('product-images/{image}/cover', [ProductImageController::class, 'cover'])->name('products.images.cover');


          Route::delete('product-images/{image}', [ProductImageController::class, 'destroy'])->name('products.images.destroy');


          Route::get('stock-movements', [StockMovementController::class, 'index'])
               ->name('stock.movements.index');



          // ---- NOTIFICAÇÕES ----
          Route::prefix('notifications')->name('notifications.')->group(function () {
               Route::get('/',             [NotificationController::class, 'index'])->name('index');
               Route::get('/{id}/read',    [NotificationController::class, 'markAsRead'])->name('read');
               Route::put('/read-all',     [NotificationController::class, 'readAll'])->name('readAll');
               Route::delete('/',          [NotificationController::class, 'destroyAll'])->name('destroyAll');
          });




          // ---- AJUSTE EM MASSA DE ESTOQUE ----
          Route::post('stock/bulk-adjust', [StockBulkController::class, 'adjust'])
               ->name('stock.bulk-adjust');

             
// ---- CUPONS ----
Route::prefix('coupons')->name('coupons.')->group(function () {
    Route::get('/',              [CouponController::class, 'index'])  ->name('index');
    Route::get('/create',        [CouponController::class, 'create']) ->name('create');
    Route::post('/',             [CouponController::class, 'store'])  ->name('store');
    Route::get('/{coupon}/edit', [CouponController::class, 'edit'])   ->name('edit');
    Route::put('/{coupon}',      [CouponController::class, 'update']) ->name('update');
    Route::delete('/{coupon}',   [CouponController::class, 'destroy'])->name('destroy');
});

// ---- PROMOÇÕES ----
Route::prefix('promotions')->name('promotions.')->group(function () {
    Route::get('/',                 [PromotionController::class, 'index'])  ->name('index');
    Route::get('/create',           [PromotionController::class, 'create']) ->name('create');
    Route::post('/',                [PromotionController::class, 'store'])  ->name('store');
    Route::get('/{promotion}/edit', [PromotionController::class, 'edit'])   ->name('edit');
    Route::put('/{promotion}',      [PromotionController::class, 'update']) ->name('update');
    Route::delete('/{promotion}',   [PromotionController::class, 'destroy'])->name('destroy');
});



// ---- PEDIDOS ----
Route::prefix('orders')->name('orders.')->group(function () {
    Route::get('/',                          [OrderController::class, 'index'])         ->name('index');
    Route::get('/{order}',                   [OrderController::class, 'show'])          ->name('show');
    Route::post('/{order}/approve',          [OrderController::class, 'approvePayment'])->name('approve');
    Route::post('/{order}/reject',           [OrderController::class, 'rejectPayment']) ->name('reject');
    Route::post('/{order}/shipped',          [OrderController::class, 'markShipped'])   ->name('shipped');
    Route::post('/{order}/delivered', [OrderController::class, 'markDelivered'])->name('delivered');
});



// ---- ZONAS DE FRETE ----
Route::prefix('shipping-zones')->name('shipping-zones.')->group(function () {
    Route::get('/',                          [ShippingZoneController::class, 'index'])       ->name('index');
    Route::get('/create',                    [ShippingZoneController::class, 'create'])      ->name('create');
    Route::post('/',                         [ShippingZoneController::class, 'store'])       ->name('store');
    Route::get('/{shippingZone}/edit',       [ShippingZoneController::class, 'edit'])        ->name('edit');
    Route::put('/{shippingZone}',            [ShippingZoneController::class, 'update'])      ->name('update');
    Route::delete('/{shippingZone}',         [ShippingZoneController::class, 'destroy'])     ->name('destroy');
    Route::put('/{shippingZone}/toggle',     [ShippingZoneController::class, 'toggleActive'])->name('toggle');
});


Route::prefix('withdrawals')->name('withdrawals.')->group(function () {
    Route::get('/',                      [WithdrawalController::class, 'index'])   ->name('index');
    Route::get('/{withdrawal}',          [WithdrawalController::class, 'show'])    ->name('show');
    Route::post('/{withdrawal}/approve', [WithdrawalController::class, 'approve']) ->name('approve');
    Route::post('/{withdrawal}/paid',    [WithdrawalController::class, 'markPaid'])->name('paid');
    Route::post('/{withdrawal}/reject',  [WithdrawalController::class, 'reject'])  ->name('reject');
});


Route::prefix('settings')->name('settings.')->group(function () {
    Route::get('/',  [SettingController::class, 'edit'])  ->name('edit');
    Route::put('/',  [SettingController::class, 'update'])->name('update');
});


Route::prefix('disputes')->name('disputes.')->group(function () {
    Route::get('/',                          [DisputeController::class, 'index'])         ->name('index');
    Route::get('/{dispute}',                 [DisputeController::class, 'show'])          ->name('show');
    Route::post('/{dispute}/message',        [DisputeController::class, 'message'])       ->name('message');
    Route::post('/{dispute}/resolve-buyer',  [DisputeController::class, 'resolveBuyer'])  ->name('resolve-buyer');
    Route::post('/{dispute}/resolve-seller', [DisputeController::class, 'resolveSeller']) ->name('resolve-seller');
    Route::post('/{dispute}/resolve-partial',[DisputeController::class, 'resolvePartial'])->name('resolve-partial');
});

     });
