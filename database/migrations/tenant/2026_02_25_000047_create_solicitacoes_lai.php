<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('tenant')->create('solicitacoes_lai', function (Blueprint $table) {
            $table->id();
            $table->string('protocolo', 20)->unique();
            $table->string('nome_solicitante');
            $table->string('email_solicitante');
            $table->text('cpf_solicitante');
            $table->string('telefone_solicitante', 20)->nullable();
            $table->string('assunto');
            $table->text('descricao');
            $table->string('status', 30)->default('recebida');
            $table->string('classificacao_resposta', 30)->nullable();
            $table->text('resposta')->nullable();
            $table->unsignedBigInteger('respondido_por')->nullable();
            $table->datetime('data_resposta')->nullable();
            $table->datetime('data_prorrogacao')->nullable();
            $table->text('justificativa_prorrogacao')->nullable();
            $table->date('prazo_legal');
            $table->date('prazo_estendido')->nullable();
            $table->unsignedBigInteger('tenant_id');
            $table->timestamps();

            $table->foreign('respondido_por')->references('id')->on('users')->nullOnDelete();
            // tenant_id sem FK — tabela tenants fica no banco principal, nao no tenant
            $table->index('status');
            $table->index('tenant_id');
            $table->index('prazo_legal');
        });

        Schema::connection('tenant')->create('historico_solicitacoes_lai', function (Blueprint $table) {
            $table->id();
            $table->foreignId('solicitacao_lai_id')->constrained('solicitacoes_lai')->cascadeOnDelete();
            $table->string('status_anterior', 30)->nullable();
            $table->string('status_novo', 30);
            $table->text('observacao')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->index('solicitacao_lai_id');
        });

        // Nota: imutabilidade (append-only) protegida via Model booted()
        // com RuntimeException. Nao usar triggers MySQL aqui pois
        // bloqueiam rollback do DatabaseTransactions nos testes.
        // Mesmo padrao de HistoricoAlteracao (IMP-032).
    }

    public function down(): void
    {
        Schema::connection('tenant')->dropIfExists('historico_solicitacoes_lai');
        Schema::connection('tenant')->dropIfExists('solicitacoes_lai');
    }
};
