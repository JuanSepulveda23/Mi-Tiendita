<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('expiration_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->integer('days_remaining'); // Días hasta vencimiento
            $table->enum('risk_level', ['low', 'high', 'urgent']); // low: 3+ días, high: 2-3 días, urgent: <= 1 día
            $table->integer('stock_at_alert'); // Stock cuando se creó la alerta
            $table->decimal('avg_daily_sales', 8, 2)->nullable(); // Promedio de ventas diarias
            $table->integer('rotation_days')->nullable(); // Cuántos días durará el stock actual
            $table->boolean('will_expire')->default(false); // ¿Se vencerá antes de venderse?
            $table->timestamp('dismissed_at')->nullable(); // Cuándo fue descartada
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expiration_alerts');
    }
};
