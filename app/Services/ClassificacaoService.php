<?php

namespace App\Services;

use App\Enums\ClassificacaoSigilo;
use App\Models\Contrato;
use App\Models\User;
use Carbon\Carbon;

class ClassificacaoService
{
    /**
     * Classifica o sigilo de um contrato (RN-403, RN-404).
     *
     * @throws \InvalidArgumentException Se classificacao != publico e sem justificativa (RN-401)
     */
    public static function classificar(
        Contrato $contrato,
        ClassificacaoSigilo $classificacao,
        ?string $justificativa,
        User $user,
        string $ip
    ): void {
        if ($classificacao->requerJustificativa() && empty($justificativa)) {
            throw new \InvalidArgumentException(
                'Justificativa é obrigatória para classificação ' . $classificacao->label()
            );
        }

        $classificacaoAnterior = $contrato->classificacao_sigilo;

        $contrato->update([
            'classificacao_sigilo' => $classificacao->value,
            'classificado_por' => $user->id,
            'data_classificacao' => now()->toDateString(),
            'justificativa_sigilo' => $classificacao->requerJustificativa() ? $justificativa : null,
        ]);

        AuditoriaService::registrar(
            $contrato,
            'classificacao_sigilo',
            $classificacaoAnterior instanceof ClassificacaoSigilo
                ? $classificacaoAnterior->value
                : $classificacaoAnterior,
            $classificacao->value,
            $user,
            $ip
        );
    }

    /**
     * Desclassifica um contrato para publico (RN-404).
     */
    public static function desclassificar(
        Contrato $contrato,
        User $user,
        string $ip
    ): void {
        self::classificar(
            $contrato,
            ClassificacaoSigilo::Publico,
            null,
            $user,
            $ip
        );
    }

    /**
     * Verifica e desclassifica contratos cujo prazo de sigilo expirou (RN-402).
     * Retorna a quantidade de contratos desclassificados.
     */
    public static function verificarDesclassificacaoAutomatica(): int
    {
        $desclassificados = 0;

        $contratos = Contrato::withoutGlobalScopes()
            ->where('classificacao_sigilo', '!=', ClassificacaoSigilo::Publico->value)
            ->whereNotNull('data_classificacao')
            ->get();

        foreach ($contratos as $contrato) {
            $classificacao = $contrato->classificacao_sigilo instanceof ClassificacaoSigilo
                ? $contrato->classificacao_sigilo
                : ClassificacaoSigilo::from($contrato->classificacao_sigilo);

            $prazoAnos = $classificacao->prazoAnos();

            if ($prazoAnos <= 0) {
                continue;
            }

            $dataLimite = Carbon::parse($contrato->data_classificacao)->addYears($prazoAnos);

            if (now()->greaterThanOrEqualTo($dataLimite)) {
                $contrato->update([
                    'classificacao_sigilo' => ClassificacaoSigilo::Publico->value,
                    'justificativa_sigilo' => null,
                ]);

                $desclassificados++;
            }
        }

        return $desclassificados;
    }
}
