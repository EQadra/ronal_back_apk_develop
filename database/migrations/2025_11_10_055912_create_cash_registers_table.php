<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cash_registers', function (Blueprint $table) {
            $table->id();

            // 🔹 Multi-tenant
            $table->foreignId('company_id')
                ->constrained()
                ->cascadeOnDelete();

            // 🔹 Nombre de la caja
            $table->string('name')->default('Principal');

            // 🔹 APERTURA
            $table->decimal('opening_cash_pen', 15, 2)->default(0);
            $table->decimal('opening_cash_bob', 15, 2)->default(0);
            $table->decimal('opening_cash_usd', 15, 2)->default(0);
            $table->decimal('opening_gold', 10, 3)->default(0);

            // 🔹 CIERRE
            $table->decimal('closing_cash_pen', 15, 2)->nullable();
            $table->decimal('closing_cash_bob', 15, 2)->nullable();
            $table->decimal('closing_cash_usd', 15, 2)->nullable();
            $table->decimal('closing_gold', 10, 3)->nullable();

            // 🔹 SALDOS FINALES
            $table->decimal('balance_pen', 15, 2)->default(0);
            $table->decimal('balance_bob', 15, 2)->default(0);
            $table->decimal('balance_usd', 15, 2)->default(0);

            // 🔹 ESTADO
            $table->enum('status', ['open', 'closed'])->default('closed');

            // 🔹 USUARIOS
            $table->foreignId('opened_by')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('closed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // 🔹 Fecha de operación
            $table->date('date');

            $table->timestamps();

            // 🔹 Índices SaaS
            $table->index(['company_id', 'date']);
            $table->index(['status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_registers');
    }
};
