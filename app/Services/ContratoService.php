<?php

namespace App\Services;

use App\Enums\NivelRisco;
use App\Enums\StatusContrato;
use App\Models\Contrato;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ContratoService
{
    /**
     * Gera numero sequencial no formato NNN/AAAA (RN-007).
     */
    public static function gerarNumero(string $ano): string
    {
        $ultimo = Contrato::where('ano', $ano)
            ->orderByRaw('CAST(SUBSTRING_INDEX(numero, "/", 1) AS UNSIGNED) DESC')
            ->value('numero');

        if ($ultimo) {
            $seq = (int) explode('/', $ultimo)[0] + 1;
        } else {
            $seq = 1;
        }

        return str_pad($seq, 3, '0', STR_PAD_LEFT) . '/' . $ano;
    }

    /**
     * Calcula prazo em meses a partir das datas (RN-031).
     */
    public static function calcularPrazoMeses(string $dataInicio, string $dataFim): int
    {
        $inicio = Carbon::parse($dataInicio);
        $fim = Carbon::parse($dataFim);

        return (int) $inicio->diffInMonths($fim);
    }

    /**
     * Cria um contrato completo com fiscal e auditoria.
     */
    public static function criar(array $dados, ?array $dadosFiscal, User $user, string $ip): Contrato
    {
        return DB::connection('tenant')->transaction(function () use ($dados, $dadosFiscal, $user, $ip) {
            // Gera numero automatico (RN-007)
            $ano = $dados['ano'] ?? date('Y');
            $dados['numero'] = self::gerarNumero($ano);
            $dados['ano'] = $ano;

            // Status inicial = vigente (RN-005)
            $dados['status'] = StatusContrato::Vigente->value;

            // Calcula prazo em meses (RN-031)
            $dados['prazo_meses'] = self::calcularPrazoMeses($dados['data_inicio'], $dados['data_fim']);

            // Score inicial
            $dados['score_risco'] = 0;
            $dados['nivel_risco'] = NivelRisco::Baixo->value;
            $dados['percentual_executado'] = 0;

            // Cria o contrato
            $contrato = Contrato::create($dados);

            // Designa fiscal se dados fornecidos (RN-024)
            if ($dadosFiscal && ! empty($dadosFiscal['fiscal_nome'])) {
                FiscalService::designar($contrato, $dadosFiscal);
            }

            // Calcula score de risco (RN-029)
            $contrato->load('fiscalAtual', 'documentos');
            $risco = RiscoService::calcular($contrato);
            $contrato->updateQuietly([
                'score_risco' => $risco['score'],
                'nivel_risco' => $risco['nivel']->value,
            ]);

            // Registra auditoria de criacao (RN-036)
            AuditoriaService::registrarCriacao($contrato, $contrato->getAttributes(), $user, $ip);

            return $contrato->fresh();
        });
    }

    /**
     * Atualiza um contrato existente com auditoria.
     */
    public static function atualizar(Contrato $contrato, array $dados, User $user, string $ip): Contrato
    {
        return DB::connection('tenant')->transaction(function () use ($contrato, $dados, $user, $ip) {
            // Recalcula prazo se datas mudaram (RN-031)
            $dataInicio = $dados['data_inicio'] ?? $contrato->data_inicio->toDateString();
            $dataFim = $dados['data_fim'] ?? $contrato->data_fim->toDateString();
            $dados['prazo_meses'] = self::calcularPrazoMeses($dataInicio, $dataFim);

            // Captura valores originais para auditoria
            $originais = $contrato->getAttributes();

            // Atualiza o contrato
            $contrato->update($dados);

            // Recalcula score de risco (RN-029)
            $contrato->load('fiscalAtual', 'documentos');
            $risco = RiscoService::calcular($contrato);
            $contrato->updateQuietly([
                'score_risco' => $risco['score'],
                'nivel_risco' => $risco['nivel']->value,
            ]);

            // Registra alteracoes na auditoria (RN-036)
            AuditoriaService::registrarAlteracoes($contrato, $originais, $contrato->getAttributes(), $user, $ip);

            return $contrato->fresh();
        });
    }
}
