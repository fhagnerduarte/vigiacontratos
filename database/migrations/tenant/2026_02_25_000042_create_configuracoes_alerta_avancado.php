<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('configuracoes_alerta_avancado', function (Blueprint $table) {
            $table->id();
            $table->string('tipo_evento', 50)->unique();
            $table->integer('dias_inatividade')->nullable()->comment('Dias sem movimentacao para ContratoParado');
            $table->integer('dias_sem_relatorio')->nullable()->comment('Dias sem relatorio fiscal para FiscalSemRelatorio');
            $table->decimal('percentual_limite_valor', 5, 2)->nullable()->comment('Limite % para AditivoAcimaLimite (padrao 25%)');
            $table->boolean('is_ativo')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('configuracoes_alerta_avancado');
    }
};
