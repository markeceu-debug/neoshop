<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('variant_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('variant_id')->constrained()->cascadeOnDelete();
            $table->string('value');                    
            $table->string('color_hex', 7)->nullable(); 
            $table->decimal('price', 10, 2)->nullable();
            $table->integer('stock')->default(0);
            $table->string('image')->nullable();
            $table->boolean('active')->default(true);
            $table->integer('position')->default(0);
            $table->timestamps();

            $table->index(['variant_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('variant_items');
    }
};