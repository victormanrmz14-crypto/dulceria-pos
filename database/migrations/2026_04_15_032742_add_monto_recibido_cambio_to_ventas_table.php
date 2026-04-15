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
        Schema::table('ventas', function (Blueprint $table) {
            $table->decimal('monto_recibido', 12, 2)->nullable()->after('total');
            $table->decimal('cambio', 12, 2)->nullable()->after('monto_recibido');
        });
    }

    public function down(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->dropColumn(['monto_recibido', 'cambio']);
        });
    }
};
