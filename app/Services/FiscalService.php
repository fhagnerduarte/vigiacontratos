<?php

namespace App\Services;

use App\Models\Contrato;
use App\Models\Fiscal;

class FiscalService
{
    /**
     * Designa o primeiro fiscal para um contrato (RN-024).
     */
    public static function designar(Contrato $contrato, array $dados): Fiscal
    {
        return Fiscal::create([
            'contrato_id' => $contrato->id,
            'nome' => $dados['fiscal_nome'],
            'matricula' => $dados['fiscal_matricula'],
            'cargo' => $dados['fiscal_cargo'],
            'email' => $dados['fiscal_email'] ?? null,
            'data_inicio' => now()->toDateString(),
            'is_atual' => true,
        ]);
    }

    /**
     * Troca o fiscal atual do contrato (RN-034, RN-035).
     * O fiscal anterior e desativado — nunca deletado.
     */
    public static function trocar(Contrato $contrato, array $dadosNovoFiscal): Fiscal
    {
        // Desativa o fiscal atual
        $fiscalAtual = $contrato->fiscalAtual;
        if ($fiscalAtual) {
            $fiscalAtual->update([
                'is_atual' => false,
                'data_fim' => now()->toDateString(),
            ]);
        }

        // Cria o novo fiscal
        return Fiscal::create([
            'contrato_id' => $contrato->id,
            'nome' => $dadosNovoFiscal['nome'],
            'matricula' => $dadosNovoFiscal['matricula'],
            'cargo' => $dadosNovoFiscal['cargo'],
            'email' => $dadosNovoFiscal['email'] ?? null,
            'data_inicio' => now()->toDateString(),
            'is_atual' => true,
        ]);
    }
}
