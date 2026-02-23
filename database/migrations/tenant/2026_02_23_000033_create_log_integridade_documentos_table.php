<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('log_integridade_documentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('documento_id')
                ->constrained('documentos')
                ->cascadeOnDelete();
            $table->char('hash_esperado', 64);
            $table->char('hash_calculado', 64)->nullable();
            $table->enum('status', ['ok', 'divergente', 'arquivo_ausente']);
            $table->datetime('detectado_em');
            $table->timestamp('created_at')->useCurrent();

            $table->index('documento_id');
            $table->index(['status', 'detectado_em']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('log_integridade_documentos');
    }
};
