<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alertas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contrato_id')->constrained('contratos')->cascadeOnDelete();
            $table->string('tipo_evento', 30);
            $table->string('prioridade', 20);
            $table->string('status', 20)->default('pendente');
            $table->integer('dias_para_vencimento');
            $table->integer('dias_antecedencia_config');
            $table->date('data_vencimento');
            $table->datetime('data_disparo');
            $table->text('mensagem');
            $table->integer('tentativas_envio')->default(0);

            // Visualizacao
            $table->foreignId('visualizado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->datetime('visualizado_em')->nullable();

            // Resolucao
            $table->foreignId('resolvido_por')->nullable()->constrained('users')->nullOnDelete();
            $table->datetime('resolvido_em')->nullable();

            $table->timestamps();

            // Indice composto para deduplicacao no service layer (RN-016)
            $table->index(['contrato_id', 'tipo_evento', 'dias_antecedencia_config'], 'alertas_dedup_idx');

            // Indices de consulta
            $table->index('status');
            $table->index('prioridade');
            $table->index('data_vencimento');
            $table->index(['contrato_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alertas');
    }
};
