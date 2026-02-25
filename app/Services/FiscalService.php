<?php

namespace App\Services;

use App\Enums\TipoFiscal;
use App\Models\Contrato;
use App\Models\Fiscal;
use App\Models\Servidor;

class FiscalService
{
    /**
     * Designa um fiscal para um contrato (RN-024).
     * Busca o servidor pelo ID e cria snapshot dos dados.
     */
    public static function designar(Contrato $contrato, array $dados, TipoFiscal $tipoFiscal = TipoFiscal::Titular): Fiscal
    {
        $servidor = Servidor::findOrFail($dados['servidor_id']);

        return Fiscal::create([
            'contrato_id' => $contrato->id,
            'servidor_id' => $servidor->id,
            'nome' => $servidor->nome,
            'matricula' => $servidor->matricula,
            'cargo' => $servidor->cargo,
            'email' => $servidor->email,
            'data_inicio' => now()->toDateString(),
            'is_atual' => true,
            'tipo_fiscal' => $tipoFiscal->value,
            'portaria_designacao' => $dados['portaria_designacao'] ?? null,
        ]);
    }

    /**
     * Designa um fiscal substituto para um contrato (Lei 14.133 art. 117).
     * Nao desativa o fiscal titular — ambos coexistem.
     */
    public static function designarSubstituto(Contrato $contrato, array $dados): Fiscal
    {
        // Desativa substituto anterior, se existir
        $substitutoAtual = $contrato->fiscalSubstituto;
        if ($substitutoAtual) {
            $substitutoAtual->update([
                'is_atual' => false,
                'data_fim' => now()->toDateString(),
            ]);
        }

        return self::designar($contrato, $dados, TipoFiscal::Substituto);
    }

    /**
     * Troca o fiscal titular do contrato (RN-034, RN-035).
     * O fiscal anterior e desativado — nunca deletado.
     */
    public static function trocar(Contrato $contrato, array $dadosNovoFiscal): Fiscal
    {
        // Desativa o fiscal titular atual
        $fiscalAtual = $contrato->fiscalAtual;
        if ($fiscalAtual) {
            $fiscalAtual->update([
                'is_atual' => false,
                'data_fim' => now()->toDateString(),
            ]);
        }

        return self::designar($contrato, $dadosNovoFiscal, TipoFiscal::Titular);
    }
}
