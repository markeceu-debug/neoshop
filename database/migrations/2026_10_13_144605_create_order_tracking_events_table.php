<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_tracking_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();

            // Status no momento do evento
            $table->string('status', 50);

            // Título curto exibido no timeline
            $table->string('title');

            // Descrição mais longa
            $table->text('description')->nullable();

            // Localização (opcional)
            $table->string('location')->nullable();

            // Ícone/código para o front
            $table->string('icon', 50)->default('circle');

            // Quem gerou o evento (admin, vendedor, sistema)
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            $table->index(['order_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_tracking_events');
    }
};