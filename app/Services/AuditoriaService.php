<?php

namespace App\Services;

use App\Models\HistoricoAlteracao;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class AuditoriaService
{
    /**
     * Registra uma unica alteracao no historico (RN-036).
     * role_nome e capturado como snapshot do perfil atual (RN-341).
     */
    public static function registrar(
        Model $entity,
        string $campo,
        ?string $anterior,
        ?string $novo,
        User $user,
        string $ip
    ): void {
        HistoricoAlteracao::create([
            'auditable_type' => $entity->getMorphClass(),
            'auditable_id' => $entity->getKey(),
            'campo_alterado' => $campo,
            'valor_anterior' => $anterior,
            'valor_novo' => $novo,
            'user_id' => $user->id,
            'role_nome' => $user->role->nome ?? 'sem_perfil',
            'ip_address' => $ip,
            'created_at' => now(),
        ]);
    }

    /**
     * Registra a criacao de uma entidade — todos os campos com valor_anterior=null.
     */
    public static function registrarCriacao(
        Model $entity,
        array $dados,
        User $user,
        string $ip
    ): void {
        $camposIgnorados = ['id', 'created_at', 'updated_at', 'deleted_at'];

        foreach ($dados as $campo => $valor) {
            if (in_array($campo, $camposIgnorados) || is_null($valor)) {
                continue;
            }

            self::registrar($entity, $campo, null, (string) $valor, $user, $ip);
        }
    }

    /**
     * Compara valores originais e novos, registra apenas os campos alterados.
     */
    public static function registrarAlteracoes(
        Model $entity,
        array $originais,
        array $novos,
        User $user,
        string $ip
    ): void {
        $camposIgnorados = ['id', 'created_at', 'updated_at', 'deleted_at'];

        foreach ($novos as $campo => $valorNovo) {
            if (in_array($campo, $camposIgnorados)) {
                continue;
            }

            $valorAnterior = $originais[$campo] ?? null;

            if ((string) $valorAnterior !== (string) $valorNovo) {
                self::registrar(
                    $entity,
                    $campo,
                    is_null($valorAnterior) ? null : (string) $valorAnterior,
                    is_null($valorNovo) ? null : (string) $valorNovo,
                    $user,
                    $ip
                );
            }
        }
    }
}
