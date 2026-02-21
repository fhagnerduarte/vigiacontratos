<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('log_acesso_documentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('documento_id')->constrained('documentos')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->string('acao', 30); // AcaoLogDocumento enum
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('created_at')->useCurrent();

            // Indices para performance (RN-122, ADR-035)
            $table->index('documento_id');
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('log_acesso_documentos');
    }
};
