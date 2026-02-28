<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();

            // 🔹 Multi-tenant (SaaS)
            $table->foreignId('company_id')
                ->constrained()
                ->cascadeOnDelete();

            // 🔹 Datos del cliente / operación
            $table->string('client_name');
            $table->decimal('grams', 10, 3);
            $table->decimal('purity', 5, 2);
            $table->decimal('discount_percentage', 5, 2)->default(0);

            // 🔹 Precios
            $table->decimal('price_per_gram_pen', 12, 4)->nullable();
            $table->decimal('price_per_gram_usd', 12, 4)->nullable();
            $table->decimal('price_per_gram_bob', 12, 4)->nullable();
            $table->decimal('price_per_oz', 12, 4)->nullable();

            // 🔹 Totales
            $table->decimal('total_pen', 15, 2)->nullable();
            $table->decimal('total_usd', 15, 2)->nullable();
            $table->decimal('total_bob', 15, 2)->nullable();

            // 🔹 Tipo de cambio
            $table->decimal('exchange_rate_pen_usd', 10, 4)->nullable();

            // 🔹 Clasificación
            $table->enum('moneda', ['PEN', 'USD', 'BOB'])->default('PEN');
            $table->enum('tipo_venta', ['regular', 'empresa'])->nullable();
            $table->enum('type', ['ingreso', 'egreso'])->default('ingreso');
            $table->string('metal_type')->nullable();

            // 🔹 Hora exacta de la operación
            $table->time('hora')->nullable();

            // 🔹 Relaciones
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('cash_register_id')
                ->nullable()
                ->constrained('cash_registers')
                ->nullOnDelete();

            $table->timestamps();

            // 🔹 Índices (performance SaaS)
            $table->index(['company_id', 'created_at']);
            $table->index(['cash_register_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
