<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('anuncios', function (Blueprint $table) {
            $table->id();
            $table->string('titulo');
            $table->text('cuerpo');
            $table->enum('tipo', ['info', 'aviso', 'alerta'])->default('info');
            $table->boolean('activo')->default(true);
            $table->timestamp('expira_en')->nullable();
            $table->foreignId('creado_por')->constrained('users');
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('anuncios'); }
};
