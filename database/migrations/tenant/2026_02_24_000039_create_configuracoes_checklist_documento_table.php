<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('configuracoes_checklist_documento', function (Blueprint $table) {
            $table->id();
            $table->string('tipo_contrato', 20);
            $table->string('tipo_documento', 50);
            $table->boolean('is_ativo')->default(true);
            $table->timestamps();

            $table->unique(['tipo_contrato', 'tipo_documento'], 'uq_tipo_contrato_tipo_doc');
            $table->index('tipo_contrato', 'idx_checklist_tipo_contrato');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('configuracoes_checklist_documento');
    }
};
