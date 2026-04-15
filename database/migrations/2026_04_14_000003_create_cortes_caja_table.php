<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cortes_caja', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->restrictOnDelete();
            $table->datetime('fecha_inicio');
            $table->datetime('fecha_corte');
            $table->integer('num_transacciones')->default(0);
            $table->decimal('total_efectivo', 12, 2)->default(0);
            $table->decimal('total_tarjeta', 12, 2)->default(0);
            $table->decimal('total_general', 12, 2)->default(0);
            $table->text('notas')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cortes_caja');
    }
};
