<?php

namespace App\Services;

use App\Models\Contrato;
use App\Models\Fiscal;
use App\Models\RelatorioFiscal;
use App\Models\User;

class RelatorioFiscalService
{
    /**
     * Registra um novo relatorio fiscal e atualiza data_ultimo_relatorio do fiscal.
     *
     * @return array{relatorio: RelatorioFiscal, alerta_resolvido: bool}
     */
    public static function registrar(Contrato $contrato, array $dados, User $user): array
    {
        $fiscalId = $dados['fiscal_id'] ?? $contrato->fiscalAtual?->id;

        // Conta ocorrencias do contrato no periodo informado
        $ocorrenciasNoPeriodo = $contrato->ocorrencias()
            ->whereBetween('data_ocorrencia', [$dados['periodo_inicio'], $dados['periodo_fim']])
            ->count();

        $relatorio = RelatorioFiscal::create([
            'contrato_id' => $contrato->id,
            'fiscal_id' => $fiscalId,
            'periodo_inicio' => $dados['periodo_inicio'],
            'periodo_fim' => $dados['periodo_fim'],
            'descricao_atividades' => $dados['descricao_atividades'],
            'conformidade_geral' => $dados['conformidade_geral'] ?? true,
            'nota_desempenho' => $dados['nota_desempenho'] ?? null,
            'ocorrencias_no_periodo' => $dados['ocorrencias_no_periodo'] ?? $ocorrenciasNoPeriodo,
            'observacoes' => $dados['observacoes'] ?? null,
            'registrado_por' => $user->id,
        ]);

        // Atualiza data_ultimo_relatorio no fiscal (resolve alerta FiscalSemRelatorio)
        $alertaResolvido = false;
        if ($fiscalId) {
            $fiscal = Fiscal::find($fiscalId);
            if ($fiscal) {
                $fiscal->update(['data_ultimo_relatorio' => $dados['periodo_fim']]);
                $alertaResolvido = self::resolverAlertaFiscalSemRelatorio($contrato);
            }
        }

        AuditoriaService::registrar(
            $contrato,
            'relatorio_fiscal_registrado',
            null,
            "Relatorio fiscal #{$relatorio->id} registrado (periodo {$relatorio->periodo_inicio->format('d/m/Y')} a {$relatorio->periodo_fim->format('d/m/Y')})",
            $user
        );

        return [
            'relatorio' => $relatorio,
            'alerta_resolvido' => $alertaResolvido,
        ];
    }

    /**
     * Resolve alertas de FiscalSemRelatorio para o contrato.
     */
    private static function resolverAlertaFiscalSemRelatorio(Contrato $contrato): bool
    {
        $alertas = $contrato->alertas()
            ->where('tipo_evento', 'fiscal_sem_relatorio')
            ->whereIn('status', ['pendente', 'enviado', 'visualizado'])
            ->get();

        if ($alertas->isEmpty()) {
            return false;
        }

        foreach ($alertas as $alerta) {
            $alerta->update([
                'status' => 'resolvido',
                'resolvido_em' => now(),
            ]);
        }

        return true;
    }

    /**
     * Resumo dos relatorios fiscais do contrato.
     *
     * @return array{total: int, conformes: int, nao_conformes: int, nota_media: float|null, ultimo_relatorio: ?RelatorioFiscal}
     */
    public static function resumo(Contrato $contrato): array
    {
        $relatorios = $contrato->relatoriosFiscais()->orderByDesc('periodo_fim')->get();

        $notaMedia = null;
        $comNota = $relatorios->whereNotNull('nota_desempenho');
        if ($comNota->isNotEmpty()) {
            $notaMedia = round($comNota->avg('nota_desempenho'), 1);
        }

        return [
            'total' => $relatorios->count(),
            'conformes' => $relatorios->where('conformidade_geral', true)->count(),
            'nao_conformes' => $relatorios->where('conformidade_geral', false)->count(),
            'nota_media' => $notaMedia,
            'ultimo_relatorio' => $relatorios->first(),
        ];
    }
}
