<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dashboard_agregados', function (Blueprint $table) {
            $table->id();
            $table->date('data_agregacao')->unique();

            // Indicadores financeiros (RN-058 a RN-061)
            $table->unsignedInteger('total_contratos_ativos')->default(0);
            $table->decimal('valor_total_contratado', 15, 2)->default(0);
            $table->decimal('valor_total_executado', 15, 2)->default(0);
            $table->decimal('saldo_remanescente', 15, 2)->default(0);
            $table->decimal('ticket_medio', 15, 2)->default(0);

            // Mapa de risco (RN-062 a RN-065)
            $table->unsignedInteger('risco_baixo')->default(0);
            $table->unsignedInteger('risco_medio')->default(0);
            $table->unsignedInteger('risco_alto')->default(0);

            // Janelas de vencimento (RN-066/067)
            $table->unsignedInteger('vencendo_0_30d')->default(0);
            $table->unsignedInteger('vencendo_31_60d')->default(0);
            $table->unsignedInteger('vencendo_61_90d')->default(0);
            $table->unsignedInteger('vencendo_91_120d')->default(0);
            $table->unsignedInteger('vencendo_120p')->default(0);

            // Score de gestao (RN-075 a RN-077)
            $table->unsignedTinyInteger('score_gestao')->default(0);

            // Dados completos para flexibilidade futura
            $table->json('dados_completos')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dashboard_agregados');
    }
};
