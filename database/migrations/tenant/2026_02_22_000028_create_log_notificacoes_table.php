<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('log_notificacoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alerta_id')->constrained('alertas')->cascadeOnDelete();
            $table->string('canal', 20);
            $table->string('destinatario', 255);
            $table->datetime('data_envio');
            $table->boolean('sucesso');
            $table->text('resposta_gateway')->nullable();
            $table->integer('tentativa_numero')->default(1);
            $table->datetime('created_at');

            // Append-only: sem updated_at

            $table->index('alerta_id');
            $table->index(['alerta_id', 'canal']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('log_notificacoes');
    }
};
