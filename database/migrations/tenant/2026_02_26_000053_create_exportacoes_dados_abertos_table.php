<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exportacoes_dados_abertos', function (Blueprint $table) {
            $table->id();
            $table->string('dataset', 50);
            $table->string('formato', 10);
            $table->json('filtros')->nullable();
            $table->unsignedInteger('total_registros');
            $table->foreignId('solicitado_por')->nullable()->constrained('users')->cascadeOnDelete();
            $table->string('ip_solicitante', 45)->nullable();
            $table->timestamps();

            $table->index('dataset');
            $table->index('formato');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exportacoes_dados_abertos');
    }
};
