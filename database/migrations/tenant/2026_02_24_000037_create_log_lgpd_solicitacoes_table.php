<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('log_lgpd_solicitacoes', function (Blueprint $table) {
            $table->id();
            $table->string('tipo_solicitacao', 30);
            $table->string('entidade_tipo', 100);
            $table->unsignedBigInteger('entidade_id');
            $table->string('solicitante', 255);
            $table->string('justificativa', 500)->nullable();
            $table->string('status', 30)->default('pendente');
            $table->json('campos_anonimizados')->nullable();
            $table->foreignId('executado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('data_solicitacao')->useCurrent();
            $table->timestamp('data_execucao')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index('entidade_tipo');
            $table->index('entidade_id');
            $table->index('tipo_solicitacao');
            $table->index('status');
        });

        // Triggers de imutabilidade (append-only, ADR-057)
        DB::unprepared("
            CREATE TRIGGER trg_log_lgpd_no_update
            BEFORE UPDATE ON log_lgpd_solicitacoes
            FOR EACH ROW
            BEGIN
                SIGNAL SQLSTATE '45000'
                SET MESSAGE_TEXT = 'Tabela log_lgpd_solicitacoes e imutavel. UPDATE nao permitido.';
            END
        ");

        DB::unprepared("
            CREATE TRIGGER trg_log_lgpd_no_delete
            BEFORE DELETE ON log_lgpd_solicitacoes
            FOR EACH ROW
            BEGIN
                SIGNAL SQLSTATE '45000'
                SET MESSAGE_TEXT = 'Tabela log_lgpd_solicitacoes e imutavel. DELETE nao permitido.';
            END
        ");
    }

    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS trg_log_lgpd_no_update');
        DB::unprepared('DROP TRIGGER IF EXISTS trg_log_lgpd_no_delete');
        Schema::dropIfExists('log_lgpd_solicitacoes');
    }
};
