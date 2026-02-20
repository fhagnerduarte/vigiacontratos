<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('historico_alteracoes', function (Blueprint $table) {
            $table->id();
            $table->string('auditable_type', 255);
            $table->unsignedBigInteger('auditable_id');
            $table->string('campo_alterado', 255);
            $table->text('valor_anterior')->nullable();
            $table->text('valor_novo')->nullable();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->string('role_nome', 100);
            $table->string('ip_address', 45);
            $table->timestamp('created_at')->useCurrent();

            $table->index(['auditable_type', 'auditable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('historico_alteracoes');
    }
};
