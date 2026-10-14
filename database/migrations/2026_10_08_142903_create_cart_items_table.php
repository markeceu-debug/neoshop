<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cart_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('variant_item_id')->nullable()->constrained()->nullOnDelete();

            // Item do carrinho É o próprio stockable quando não há variante:
            // - se variant_item_id NOT NULL → produto é o pai da variante
            // - se variant_item_id NULL e product_id NOT NULL → produto sem variante

            $table->integer('quantity');
            $table->decimal('unit_price', 10, 2);
            $table->decimal('subtotal', 12, 2);

            // Vínculo com a reserva de estoque
            $table->foreignId('reservation_id')
                  ->nullable()
                  ->constrained('stock_reservations')
                  ->nullOnDelete();

            $table->timestamps();

            $table->unique(['cart_id', 'product_id', 'variant_item_id'], 'cart_item_unique');
            $table->index('cart_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cart_items');
    }
};