<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Reemplaza el unique global en 'nombre' por un unique compuesto (tenant_id, nombre)
     * en las tablas categorias y marcas, para que distintos tenants puedan usar el mismo nombre.
     */
    public function up(): void
    {
        foreach (['categorias', 'marcas'] as $tabla) {
            Schema::table($tabla, function (Blueprint $table) use ($tabla) {
                $table->dropUnique("{$tabla}_nombre_unique");
                $table->unique(['tenant_id', 'nombre']);
            });
        }
    }

    public function down(): void
    {
        foreach (['categorias', 'marcas'] as $tabla) {
            Schema::table($tabla, function (Blueprint $table) use ($tabla) {
                $table->dropUnique("{$tabla}_tenant_id_nombre_unique");
                $table->unique('nombre');
            });
        }
    }
};
