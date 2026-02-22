<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('configuracoes_alerta', function (Blueprint $table) {
            $table->id();
            $table->integer('dias_antecedencia');
            $table->string('prioridade_padrao', 20);
            $table->boolean('is_ativo')->default(true);
            $table->timestamps();

            $table->unique('dias_antecedencia');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('configuracoes_alerta');
    }
};
