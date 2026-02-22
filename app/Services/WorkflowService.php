<?php

namespace App\Services;

use App\Enums\EtapaWorkflow;
use App\Enums\StatusAprovacao;
use App\Models\Role;
use App\Models\User;
use App\Models\WorkflowAprovacao;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class WorkflowService
{
    /**
     * Cria o fluxo de aprovacao com 5 etapas sequenciais (RN-335).
     * Etapa 1 (Solicitacao) e auto-aprovada pelo solicitante.
     */
    public static function criarFluxo(Model $aprovavel, User $solicitante, string $ip): Collection
    {
        $etapas = EtapaWorkflow::cases();
        $registros = collect();

        foreach ($etapas as $etapa) {
            $role = Role::where('nome', $etapa->roleResponsavel())->first();

            $dados = [
                'aprovavel_type' => $aprovavel->getMorphClass(),
                'aprovavel_id' => $aprovavel->getKey(),
                'etapa' => $etapa->value,
                'etapa_ordem' => $etapa->ordem(),
                'role_responsavel_id' => $role?->id,
                'status' => StatusAprovacao::Pendente->value,
                'created_at' => now(),
            ];

            // Etapa 1 — auto-aprovada pelo solicitante
            if ($etapa === EtapaWorkflow::Solicitacao) {
                $dados['status'] = StatusAprovacao::Aprovado->value;
                $dados['user_id'] = $solicitante->id;
                $dados['parecer'] = 'Solicitacao registrada automaticamente.';
                $dados['decided_at'] = now();
            }

            $registro = WorkflowAprovacao::create($dados);
            $registros->push($registro);
        }

        return $registros;
    }

    /**
     * Aprova a etapa atual do workflow (RN-337).
     * Valida que a etapa anterior foi aprovada (bloqueio sequencial).
     */
    public static function aprovar(WorkflowAprovacao $etapa, User $user, ?string $parecer, string $ip): WorkflowAprovacao
    {
        // Valida que e etapa pendente
        if ($etapa->status !== StatusAprovacao::Pendente) {
            throw new \RuntimeException('Esta etapa ja foi processada.');
        }

        // Valida bloqueio sequencial (RN-337)
        $etapaAnterior = WorkflowAprovacao::where('aprovavel_type', $etapa->aprovavel_type)
            ->where('aprovavel_id', $etapa->aprovavel_id)
            ->where('etapa_ordem', $etapa->etapa_ordem - 1)
            ->first();

        if ($etapaAnterior && $etapaAnterior->status !== StatusAprovacao::Aprovado) {
            throw new \RuntimeException('A etapa anterior precisa ser aprovada primeiro (RN-337).');
        }

        // Atualiza a etapa (permitido enquanto status=pendente via booted())
        $etapa->update([
            'user_id' => $user->id,
            'status' => StatusAprovacao::Aprovado->value,
            'parecer' => $parecer,
            'decided_at' => now(),
        ]);

        // Registra auditoria
        AuditoriaService::registrar(
            $etapa->aprovavel,
            'workflow_aprovado',
            $etapa->etapa->label() . ' - pendente',
            $etapa->etapa->label() . ' - aprovado por ' . $user->nome,
            $user,
            $ip
        );

        return $etapa->fresh();
    }

    /**
     * Reprova a etapa atual do workflow (RN-338).
     * Parecer e obrigatorio na reprovacao.
     */
    public static function reprovar(WorkflowAprovacao $etapa, User $user, string $parecer, string $ip): WorkflowAprovacao
    {
        if ($etapa->status !== StatusAprovacao::Pendente) {
            throw new \RuntimeException('Esta etapa ja foi processada.');
        }

        if (empty($parecer)) {
            throw new \RuntimeException('Parecer e obrigatorio na reprovacao (RN-338).');
        }

        $etapa->update([
            'user_id' => $user->id,
            'status' => StatusAprovacao::Reprovado->value,
            'parecer' => $parecer,
            'decided_at' => now(),
        ]);

        // Registra auditoria
        AuditoriaService::registrar(
            $etapa->aprovavel,
            'workflow_reprovado',
            $etapa->etapa->label() . ' - pendente',
            $etapa->etapa->label() . ' - reprovado por ' . $user->nome . ': ' . $parecer,
            $user,
            $ip
        );

        return $etapa->fresh();
    }

    /**
     * Retorna a etapa atual pendente do workflow.
     */
    public static function obterEtapaAtual(Model $aprovavel): ?WorkflowAprovacao
    {
        return WorkflowAprovacao::where('aprovavel_type', $aprovavel->getMorphClass())
            ->where('aprovavel_id', $aprovavel->getKey())
            ->where('status', StatusAprovacao::Pendente->value)
            ->orderBy('etapa_ordem')
            ->first();
    }

    /**
     * Verifica se todas as etapas do workflow foram aprovadas.
     */
    public static function isAprovado(Model $aprovavel): bool
    {
        $total = WorkflowAprovacao::where('aprovavel_type', $aprovavel->getMorphClass())
            ->where('aprovavel_id', $aprovavel->getKey())
            ->count();

        if ($total === 0) {
            return false;
        }

        $aprovados = WorkflowAprovacao::where('aprovavel_type', $aprovavel->getMorphClass())
            ->where('aprovavel_id', $aprovavel->getKey())
            ->where('status', StatusAprovacao::Aprovado->value)
            ->count();

        return $total === $aprovados;
    }

    /**
     * Retorna o historico completo do workflow ordenado por etapa.
     */
    public static function obterHistorico(Model $aprovavel): Collection
    {
        return WorkflowAprovacao::where('aprovavel_type', $aprovavel->getMorphClass())
            ->where('aprovavel_id', $aprovavel->getKey())
            ->with(['roleResponsavel', 'user'])
            ->orderBy('etapa_ordem')
            ->get();
    }
}
