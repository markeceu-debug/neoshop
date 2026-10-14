<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name')->nullable();
            $table->text('description')->nullable();

            $table->enum('type', ['percent', 'fixed', 'free_shipping'])->default('percent');
            $table->decimal('value', 12, 2)->default(0);
            $table->decimal('min_purchase', 12, 2)->default(0);
            $table->decimal('max_discount', 12, 2)->nullable();

            $table->integer('usage_limit')->nullable();      // NULL = ilimitado
            $table->integer('per_user_limit')->nullable();   // NULL = ilimitado
            $table->integer('used_count')->default(0);

            $table->enum('applies_to', ['all', 'products', 'categories'])->default('all');
            $table->json('product_ids')->nullable();
            $table->json('category_ids')->nullable();

            $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->index(['active', 'expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};