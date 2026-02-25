<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('encerramentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contrato_id')->unique()->constrained('contratos')->cascadeOnDelete();
            $table->string('etapa_atual', 50)->default('verificacao_financeira');
            $table->timestamp('data_inicio')->useCurrent();

            // Etapa 1: Verificacao Financeira
            $table->boolean('verificacao_financeira_ok')->nullable();
            $table->foreignId('verificacao_financeira_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verificacao_financeira_em')->nullable();
            $table->text('verificacao_financeira_obs')->nullable();

            // Etapa 2: Termo de Recebimento Provisorio
            $table->timestamp('termo_provisorio_em')->nullable();
            $table->foreignId('termo_provisorio_por')->nullable()->constrained('users')->nullOnDelete();
            $table->integer('termo_provisorio_prazo_dias')->nullable();

            // Etapa 3: Avaliacao do Fiscal
            $table->decimal('avaliacao_fiscal_nota', 3, 1)->nullable();
            $table->text('avaliacao_fiscal_obs')->nullable();
            $table->foreignId('avaliacao_fiscal_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('avaliacao_fiscal_em')->nullable();

            // Etapa 4: Termo de Recebimento Definitivo
            $table->timestamp('termo_definitivo_em')->nullable();
            $table->foreignId('termo_definitivo_por')->nullable()->constrained('users')->nullOnDelete();

            // Etapa 5: Quitacao
            $table->timestamp('quitacao_em')->nullable();
            $table->foreignId('quitacao_por')->nullable()->constrained('users')->nullOnDelete();
            $table->text('quitacao_obs')->nullable();

            // Conclusao
            $table->date('data_encerramento_efetivo')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('encerramentos');
    }
};
