<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contratos', function (Blueprint $table) {
            $table->id();
            $table->string('numero', 50)->unique();
            $table->string('ano', 4);
            $table->text('objeto');
            $table->string('tipo', 30);
            $table->string('status', 30)->default('vigente');
            $table->string('modalidade_contratacao', 50);

            $table->foreignId('fornecedor_id')->constrained('fornecedores')->restrictOnDelete();
            $table->foreignId('secretaria_id')->constrained('secretarias')->restrictOnDelete();
            $table->string('unidade_gestora', 255)->nullable();

            $table->date('data_inicio');
            $table->date('data_fim');
            $table->integer('prazo_meses');
            $table->boolean('prorrogacao_automatica')->default(false);

            $table->decimal('valor_global', 15, 2);
            $table->decimal('valor_mensal', 15, 2)->nullable();
            $table->string('tipo_pagamento', 30)->nullable();
            $table->string('fonte_recurso', 255)->nullable();
            $table->string('dotacao_orcamentaria', 255)->nullable();
            $table->string('numero_empenho', 50)->nullable();
            $table->string('numero_processo', 50);
            $table->string('fundamento_legal', 255)->nullable();

            $table->string('categoria', 30)->nullable();
            $table->string('categoria_servico', 30)->nullable();
            $table->string('responsavel_tecnico', 255)->nullable();
            $table->string('gestor_nome', 255)->nullable();

            $table->integer('score_risco')->default(0);
            $table->string('nivel_risco', 10)->default('baixo');
            $table->decimal('percentual_executado', 5, 2)->default(0);

            $table->text('observacoes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('data_fim');
            $table->index('secretaria_id');
            $table->index('status');
            $table->index('valor_global');
            $table->index('categoria');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contratos');
    }
};
