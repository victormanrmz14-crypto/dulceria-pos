<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Tablas que reciben tenant_id (en orden de dependencia). */
    private array $tablas = [
        'users',
        'categorias',
        'marcas',
        'proveedores',
        'productos',
        'ventas',
        'detalle_ventas',
        'cortes_caja',
        'movimientos_caja',
    ];

    public function up(): void
    {
        foreach ($this->tablas as $tabla) {
            Schema::table($tabla, function (Blueprint $table) {
                // nullable para que los super-admins de plataforma puedan existir sin tenant
                $table->foreignId('tenant_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('tenants')
                    ->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        foreach (array_reverse($this->tablas) as $tabla) {
            Schema::table($tabla, function (Blueprint $table) {
                $table->dropForeign(['tenant_id']);
                $table->dropColumn('tenant_id');
            });
        }
    }
};
