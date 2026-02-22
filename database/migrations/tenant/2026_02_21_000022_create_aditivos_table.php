<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aditivos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contrato_id')->constrained('contratos')->restrictOnDelete();
            $table->integer('numero_sequencial');
            $table->string('tipo', 30);
            $table->string('status', 20)->default('vigente');
            $table->date('data_assinatura');
            $table->date('data_inicio_vigencia')->nullable();
            $table->date('nova_data_fim')->nullable();

            // Financeiro
            $table->decimal('valor_anterior_contrato', 15, 2)->nullable();
            $table->decimal('valor_acrescimo', 15, 2)->nullable();
            $table->decimal('valor_supressao', 15, 2)->nullable();
            $table->decimal('percentual_acumulado', 5, 2)->default(0);

            // Legal e justificativas
            $table->text('fundamentacao_legal');
            $table->text('justificativa');
            $table->text('justificativa_tecnica');
            $table->text('justificativa_excesso_limite')->nullable();
            $table->boolean('parecer_juridico_obrigatorio')->default(false);

            // Reequilibrio (RN-095)
            $table->text('motivo_reequilibrio')->nullable();
            $table->string('indice_utilizado', 50)->nullable();
            $table->decimal('valor_anterior_reequilibrio', 15, 2)->nullable();
            $table->decimal('valor_reajustado', 15, 2)->nullable();

            $table->text('observacoes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indices
            $table->index('contrato_id');
            $table->index(['contrato_id', 'data_assinatura']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aditivos');
    }
};
