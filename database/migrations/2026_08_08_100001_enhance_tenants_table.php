<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('tenants', function (Blueprint $table) {
            $table->boolean('activo')->default(true)->after('nombre');
            $table->enum('plan', ['trial', 'basico', 'pro'])->default('trial')->after('activo');
            $table->timestamp('plan_expira_en')->nullable()->after('plan');
            $table->text('notas')->nullable()->after('plan_expira_en');
        });
    }
    public function down(): void {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn(['activo', 'plan', 'plan_expira_en', 'notas']);
        });
    }
};
