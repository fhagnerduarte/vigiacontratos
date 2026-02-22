<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workflow_aprovacoes', function (Blueprint $table) {
            $table->id();
            $table->string('aprovavel_type');
            $table->unsignedBigInteger('aprovavel_id');
            $table->string('etapa', 30);
            $table->integer('etapa_ordem');
            $table->foreignId('role_responsavel_id')->nullable()->constrained('roles')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 20)->default('pendente');
            $table->text('parecer')->nullable();
            $table->timestamp('created_at')->nullable();

            // Sem updated_at — append-only (RN-336)

            // Indices
            $table->index(['aprovavel_type', 'aprovavel_id']);
            $table->index(['status', 'etapa_ordem']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workflow_aprovacoes');
    }
};
