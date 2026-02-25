<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Adicionar campos de fase ao checklist existente
        Schema::table('configuracoes_checklist_documento', function (Blueprint $table) {
            $table->string('fase', 50)->nullable()->after('tipo_documento');
            $table->string('descricao', 255)->nullable()->after('fase');
            $table->integer('ordem')->default(0)->after('descricao');

            // Drop unique constraint antigo e criar novo com fase
            $table->dropUnique('uq_tipo_contrato_tipo_doc');
            $table->unique(['fase', 'tipo_contrato', 'tipo_documento'], 'uq_fase_tipo_contrato_tipo_doc');
        });

        // Tabela de cache de conformidade por fase (pre-calculada)
        Schema::create('contrato_conformidade_fases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contrato_id')->constrained('contratos')->cascadeOnDelete();
            $table->string('fase', 50);
            $table->decimal('percentual_conformidade', 5, 2)->default(0);
            $table->integer('total_obrigatorios')->default(0);
            $table->integer('total_presentes')->default(0);
            $table->string('nivel_semaforo', 10)->default('vermelho');
            $table->timestamps();

            $table->unique(['contrato_id', 'fase'], 'uq_contrato_fase');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contrato_conformidade_fases');

        Schema::table('configuracoes_checklist_documento', function (Blueprint $table) {
            $table->dropUnique('uq_fase_tipo_contrato_tipo_doc');
            $table->dropColumn(['fase', 'descricao', 'ordem']);
            $table->unique(['tipo_contrato', 'tipo_documento'], 'uq_tipo_contrato_tipo_doc');
        });
    }
};
