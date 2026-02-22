<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('configuracoes_limite_aditivo', function (Blueprint $table) {
            $table->id();
            $table->string('tipo_contrato', 20)->unique();
            $table->decimal('percentual_limite', 5, 2);
            $table->boolean('is_bloqueante')->default(true);
            $table->boolean('is_ativo')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('configuracoes_limite_aditivo');
    }
};
