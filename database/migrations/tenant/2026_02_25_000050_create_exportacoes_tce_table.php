<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exportacoes_tce', function (Blueprint $table) {
            $table->id();
            $table->string('formato', 10);
            $table->json('filtros')->nullable();
            $table->unsignedInteger('total_contratos');
            $table->unsignedInteger('total_pendencias')->default(0);
            $table->string('arquivo_path', 500)->nullable();
            $table->string('arquivo_nome', 255);
            $table->foreignId('gerado_por')->constrained('users')->cascadeOnDelete();
            $table->text('observacoes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exportacoes_tce');
    }
};
