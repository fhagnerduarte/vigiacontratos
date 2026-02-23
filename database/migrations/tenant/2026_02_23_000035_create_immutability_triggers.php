<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Triggers MySQL de imutabilidade para tabelas append-only.
     * Segunda camada de protecao (primeira: Eloquent booted() nos Models).
     */
    public function up(): void
    {
        // ── historico_alteracoes ──────────────────────────────────
        DB::unprepared("
            CREATE TRIGGER trg_historico_no_update
            BEFORE UPDATE ON historico_alteracoes
            FOR EACH ROW
            BEGIN
                SIGNAL SQLSTATE '45000'
                SET MESSAGE_TEXT = 'Tabela historico_alteracoes e imutavel. UPDATE nao permitido.';
            END
        ");

        DB::unprepared("
            CREATE TRIGGER trg_historico_no_delete
            BEFORE DELETE ON historico_alteracoes
            FOR EACH ROW
            BEGIN
                SIGNAL SQLSTATE '45000'
                SET MESSAGE_TEXT = 'Tabela historico_alteracoes e imutavel. DELETE nao permitido.';
            END
        ");

        // ── login_logs ────────────────────────────────────────────
        DB::unprepared("
            CREATE TRIGGER trg_login_logs_no_update
            BEFORE UPDATE ON login_logs
            FOR EACH ROW
            BEGIN
                SIGNAL SQLSTATE '45000'
                SET MESSAGE_TEXT = 'Tabela login_logs e imutavel. UPDATE nao permitido.';
            END
        ");

        DB::unprepared("
            CREATE TRIGGER trg_login_logs_no_delete
            BEFORE DELETE ON login_logs
            FOR EACH ROW
            BEGIN
                SIGNAL SQLSTATE '45000'
                SET MESSAGE_TEXT = 'Tabela login_logs e imutavel. DELETE nao permitido.';
            END
        ");

        // ── log_acesso_documentos ─────────────────────────────────
        DB::unprepared("
            CREATE TRIGGER trg_log_acesso_no_update
            BEFORE UPDATE ON log_acesso_documentos
            FOR EACH ROW
            BEGIN
                SIGNAL SQLSTATE '45000'
                SET MESSAGE_TEXT = 'Tabela log_acesso_documentos e imutavel. UPDATE nao permitido.';
            END
        ");

        DB::unprepared("
            CREATE TRIGGER trg_log_acesso_no_delete
            BEFORE DELETE ON log_acesso_documentos
            FOR EACH ROW
            BEGIN
                SIGNAL SQLSTATE '45000'
                SET MESSAGE_TEXT = 'Tabela log_acesso_documentos e imutavel. DELETE nao permitido.';
            END
        ");

        // ── log_notificacoes ──────────────────────────────────────
        DB::unprepared("
            CREATE TRIGGER trg_log_notificacoes_no_update
            BEFORE UPDATE ON log_notificacoes
            FOR EACH ROW
            BEGIN
                SIGNAL SQLSTATE '45000'
                SET MESSAGE_TEXT = 'Tabela log_notificacoes e imutavel. UPDATE nao permitido.';
            END
        ");

        DB::unprepared("
            CREATE TRIGGER trg_log_notificacoes_no_delete
            BEFORE DELETE ON log_notificacoes
            FOR EACH ROW
            BEGIN
                SIGNAL SQLSTATE '45000'
                SET MESSAGE_TEXT = 'Tabela log_notificacoes e imutavel. DELETE nao permitido.';
            END
        ");

        // ── log_integridade_documentos ────────────────────────────
        DB::unprepared("
            CREATE TRIGGER trg_log_integridade_no_update
            BEFORE UPDATE ON log_integridade_documentos
            FOR EACH ROW
            BEGIN
                SIGNAL SQLSTATE '45000'
                SET MESSAGE_TEXT = 'Tabela log_integridade_documentos e imutavel. UPDATE nao permitido.';
            END
        ");

        DB::unprepared("
            CREATE TRIGGER trg_log_integridade_no_delete
            BEFORE DELETE ON log_integridade_documentos
            FOR EACH ROW
            BEGIN
                SIGNAL SQLSTATE '45000'
                SET MESSAGE_TEXT = 'Tabela log_integridade_documentos e imutavel. DELETE nao permitido.';
            END
        ");

        // ── workflow_aprovacoes (parcial: so bloqueia apos decisao) ──
        DB::unprepared("
            CREATE TRIGGER trg_workflow_no_update
            BEFORE UPDATE ON workflow_aprovacoes
            FOR EACH ROW
            BEGIN
                IF OLD.status != 'pendente' THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'Etapa de workflow ja concluida nao pode ser alterada.';
                END IF;
            END
        ");

        DB::unprepared("
            CREATE TRIGGER trg_workflow_no_delete
            BEFORE DELETE ON workflow_aprovacoes
            FOR EACH ROW
            BEGIN
                SIGNAL SQLSTATE '45000'
                SET MESSAGE_TEXT = 'Registros de workflow nao podem ser excluidos.';
            END
        ");
    }

    public function down(): void
    {
        $triggers = [
            'trg_historico_no_update',
            'trg_historico_no_delete',
            'trg_login_logs_no_update',
            'trg_login_logs_no_delete',
            'trg_log_acesso_no_update',
            'trg_log_acesso_no_delete',
            'trg_log_notificacoes_no_update',
            'trg_log_notificacoes_no_delete',
            'trg_log_integridade_no_update',
            'trg_log_integridade_no_delete',
            'trg_workflow_no_update',
            'trg_workflow_no_delete',
        ];

        foreach ($triggers as $trigger) {
            DB::unprepared("DROP TRIGGER IF EXISTS {$trigger}");
        }
    }
};
