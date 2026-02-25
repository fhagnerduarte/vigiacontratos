<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Ocorrencias registradas pelo fiscal durante a execucao do contrato
        Schema::create('ocorrencias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contrato_id')->constrained('contratos')->cascadeOnDelete();
            $table->foreignId('fiscal_id')->constrained('fiscais')->cascadeOnDelete();
            $table->date('data_ocorrencia');
            $table->string('tipo_ocorrencia', 50);
            $table->text('descricao');
            $table->text('providencia')->nullable();
            $table->date('prazo_providencia')->nullable();
            $table->boolean('resolvida')->default(false);
            $table->timestamp('resolvida_em')->nullable();
            $table->foreignId('resolvida_por')->nullable()->constrained('users')->nullOnDelete();
            $table->text('observacoes')->nullable();
            $table->foreignId('registrado_por')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->index(['contrato_id', 'resolvida']);
            $table->index(['contrato_id', 'tipo_ocorrencia']);
        });

        // Relatorios fiscais estruturados (Lei 14.133 art. 117)
        Schema::create('relatorios_fiscais', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contrato_id')->constrained('contratos')->cascadeOnDelete();
            $table->foreignId('fiscal_id')->constrained('fiscais')->cascadeOnDelete();
            $table->date('periodo_inicio');
            $table->date('periodo_fim');
            $table->text('descricao_atividades');
            $table->boolean('conformidade_geral')->default(true);
            $table->unsignedTinyInteger('nota_desempenho')->nullable()->comment('1-10');
            $table->unsignedInteger('ocorrencias_no_periodo')->default(0);
            $table->text('observacoes')->nullable();
            $table->foreignId('registrado_por')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->index(['contrato_id', 'periodo_fim']);
            $table->index('fiscal_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('relatorios_fiscais');
        Schema::dropIfExists('ocorrencias');
    }
};
