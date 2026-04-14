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
    Schema::create('ventas', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')
              ->constrained('users')
              ->restrictOnDelete();
        $table->string('folio', 20)->unique();
        $table->enum('metodo_pago', ['efectivo', 'tarjeta'])->default('efectivo');
        $table->decimal('subtotal', 12, 2)->default(0);
        $table->decimal('impuestos', 12, 2)->default(0);
        $table->decimal('total', 12, 2)->default(0);
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ventas');
    }
};
