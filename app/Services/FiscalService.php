<?php

namespace App\Services;

use App\Models\Contrato;
use App\Models\Fiscal;
use App\Models\Servidor;

class FiscalService
{
    /**
     * Designa o primeiro fiscal para um contrato (RN-024).
     * Busca o servidor pelo ID e cria snapshot dos dados.
     */
    public static function designar(Contrato $contrato, array $dados): Fiscal
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

        // Cria o novo fiscal a partir do servidor
        $servidor = Servidor::findOrFail($dadosNovoFiscal['servidor_id']);

        return Fiscal::create([
            'contrato_id' => $contrato->id,
            'servidor_id' => $servidor->id,
            'nome' => $servidor->nome,
            'matricula' => $servidor->matricula,
            'cargo' => $servidor->cargo,
            'email' => $servidor->email,
            'data_inicio' => now()->toDateString(),
            'is_atual' => true,
        ]);
    }
}
