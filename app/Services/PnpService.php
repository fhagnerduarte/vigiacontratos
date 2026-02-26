<?php

namespace App\Services;

use App\Enums\CategoriaServico;
use App\Enums\StatusComparativoPreco;
use App\Enums\StatusContrato;
use App\Models\ComparativoPreco;
use App\Models\Contrato;
use App\Models\PrecoReferencial;
use App\Models\Scopes\SecretariaScope;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class PnpService
{
    /**
     * Listar precos referenciais com filtros e paginacao.
     */
    public static function listarPrecos(array $filtros = []): LengthAwarePaginator
    {
        $query = PrecoReferencial::with('registrador');

        if (! empty($filtros['categoria_servico'])) {
            $query->where('categoria_servico', $filtros['categoria_servico']);
        }

        if (isset($filtros['is_ativo'])) {
            $query->where('is_ativo', (bool) $filtros['is_ativo']);
        }

        if (! empty($filtros['vigentes'])) {
            $query->vigentes();
        }

        if (! empty($filtros['search'])) {
            $query->where('descricao', 'like', '%' . $filtros['search'] . '%');
        }

        $perPage = min((int) ($filtros['per_page'] ?? 15), 100);

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    /**
     * Registrar um novo preco referencial.
     */
    public static function registrarPreco(array $dados, int $userId): PrecoReferencial
    {
        $dados['registrado_por'] = $userId;

        $preco = PrecoReferencial::create($dados);

        WebhookService::disparar('pnp.preco.registrado', [
            'id' => $preco->id,
            'descricao' => $preco->descricao,
            'categoria_servico' => $preco->categoria_servico->value,
            'preco_maximo' => $preco->preco_maximo,
        ]);

        return $preco;
    }

    /**
     * Atualizar um preco referencial existente.
     */
    public static function atualizarPreco(int $id, array $dados): PrecoReferencial
    {
        $preco = PrecoReferencial::findOrFail($id);
        $preco->update($dados);

        return $preco->fresh();
    }

    /**
     * Gerar comparativo para um contrato especifico.
     * Busca preco referencial vigente pela categoria_servico do contrato.
     * Retorna null se nao houver referencia para a categoria.
     */
    public static function compararContrato(Contrato $contrato, int $userId): ?ComparativoPreco
    {
        if (! $contrato->categoria_servico) {
            return null;
        }

        $precoRef = PrecoReferencial::vigentes()
            ->where('categoria_servico', $contrato->categoria_servico->value)
            ->orderBy('data_referencia', 'desc')
            ->first();

        if (! $precoRef) {
            return null;
        }

        $valorContrato = (float) $contrato->valor_mensal > 0
            ? (float) $contrato->valor_mensal
            : (float) $contrato->valor_global;

        $valorReferencia = (float) $precoRef->preco_maximo;

        if ($valorReferencia <= 0) {
            return null;
        }

        $percentualDiferenca = (($valorContrato - $valorReferencia) / $valorReferencia) * 100;

        $status = self::determinarStatus($percentualDiferenca);

        $comparativo = ComparativoPreco::create([
            'contrato_id' => $contrato->id,
            'preco_referencial_id' => $precoRef->id,
            'valor_contrato' => $valorContrato,
            'valor_referencia' => $valorReferencia,
            'percentual_diferenca' => round($percentualDiferenca, 2),
            'status_comparativo' => $status->value,
            'gerado_por' => $userId,
        ]);

        WebhookService::disparar('pnp.comparativo.gerado', [
            'contrato_id' => $contrato->id,
            'contrato_numero' => $contrato->numero,
            'status' => $status->value,
            'percentual_diferenca' => round($percentualDiferenca, 2),
        ]);

        return $comparativo;
    }

    /**
     * Gerar comparativo em lote para todos os contratos vigentes.
     */
    public static function gerarComparativoGeral(int $userId): array
    {
        $contratos = Contrato::withoutGlobalScope(SecretariaScope::class)
            ->where('status', StatusContrato::Vigente->value)
            ->whereNotNull('categoria_servico')
            ->get();

        $resultado = [
            'total_contratos' => $contratos->count(),
            'comparados' => 0,
            'sem_referencia' => 0,
            'adequados' => 0,
            'atencao' => 0,
            'sobrepreco' => 0,
        ];

        foreach ($contratos as $contrato) {
            $comparativo = self::compararContrato($contrato, $userId);

            if ($comparativo === null) {
                $resultado['sem_referencia']++;
            } else {
                $resultado['comparados']++;
                match ($comparativo->status_comparativo) {
                    StatusComparativoPreco::Adequado => $resultado['adequados']++,
                    StatusComparativoPreco::Atencao => $resultado['atencao']++,
                    StatusComparativoPreco::Sobrepreco => $resultado['sobrepreco']++,
                };
            }
        }

        return $resultado;
    }

    /**
     * Indicadores agregados do PNP.
     */
    public static function indicadores(): array
    {
        $totalReferencias = PrecoReferencial::ativos()->count();

        $categoriasCoverage = PrecoReferencial::ativos()
            ->distinct('categoria_servico')
            ->count('categoria_servico');

        $comparativosRecentes = ComparativoPreco::query()
            ->selectRaw('status_comparativo, COUNT(*) as total')
            ->groupBy('status_comparativo')
            ->pluck('total', 'status_comparativo');

        $sobreprecoCount = $comparativosRecentes->get(StatusComparativoPreco::Sobrepreco->value, 0);
        $totalComparados = $comparativosRecentes->sum();

        $mediaPercentual = ComparativoPreco::where('status_comparativo', StatusComparativoPreco::Sobrepreco->value)
            ->avg('percentual_diferenca') ?? 0;

        $economiaPotencial = ComparativoPreco::where('status_comparativo', StatusComparativoPreco::Sobrepreco->value)
            ->selectRaw('SUM(valor_contrato - valor_referencia) as economia')
            ->value('economia') ?? 0;

        return [
            'total_referencias' => $totalReferencias,
            'categorias_cobertas' => $categoriasCoverage,
            'contratos_sobrepreco' => $sobreprecoCount,
            'percentual_sobrepreco_medio' => round((float) $mediaPercentual, 2),
            'economia_potencial' => round((float) $economiaPotencial, 2),
            'total_comparados' => $totalComparados,
        ];
    }

    /**
     * Historico de precos por categoria.
     */
    public static function historicoPorCategoria(CategoriaServico $categoria): Collection
    {
        return PrecoReferencial::where('categoria_servico', $categoria->value)
            ->orderBy('data_referencia', 'asc')
            ->get(['id', 'descricao', 'preco_minimo', 'preco_mediano', 'preco_maximo', 'fonte', 'data_referencia', 'vigencia_ate', 'is_ativo']);
    }

    /**
     * Determinar status comparativo baseado no percentual de diferenca.
     */
    private static function determinarStatus(float $percentualDiferenca): StatusComparativoPreco
    {
        if ($percentualDiferenca > 25) {
            return StatusComparativoPreco::Sobrepreco;
        }

        if ($percentualDiferenca > 10) {
            return StatusComparativoPreco::Atencao;
        }

        return StatusComparativoPreco::Adequado;
    }
}
