<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipping_zones', function (Blueprint $table) {
            $table->id();

            // Localização
            $table->string('province', 100);
            $table->string('municipality', 100);

            // Motoboy padrão
            $table->decimal('standard_fee', 12, 2)->default(0);
            $table->integer('standard_hours')->default(6);       // horas estimadas
// Adiciona em shipping_zones
$table->decimal('max_fee', 12, 2)->default(2000);
            // Motoboy expresso
            $table->decimal('express_fee', 12, 2)->default(0);
            $table->integer('express_hours')->default(2);

            // Retirada
            $table->boolean('pickup_enabled')->default(true);
            $table->string('pickup_address')->nullable();

            // Frete grátis
            $table->decimal('free_above', 12, 2)->nullable();

            // Metadados
            $table->boolean('active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['province', 'municipality']);
            $table->index('active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipping_zones');
    }
};