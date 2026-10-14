<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promotions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('banner')->nullable();

            $table->enum('type', ['percent', 'fixed', 'buy_x_get_y'])->default('percent');
            $table->decimal('value', 12, 2)->default(0);

            $table->enum('applies_to', ['all', 'products', 'categories'])->default('all');
            $table->json('product_ids')->nullable();
            $table->json('category_ids')->nullable();

            // Para buy_x_get_y
            $table->integer('buy_qty')->nullable();
            $table->integer('get_qty')->nullable();

            $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->integer('priority')->default(0);
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->index(['active', 'starts_at', 'expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promotions');
    }
};