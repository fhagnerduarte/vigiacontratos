<?php

namespace Database\Seeders;

use App\Models\Contrato;
use App\Models\ExecucaoFinanceira;
use App\Models\User;
use Illuminate\Database\Seeder;

class ExecucaoFinanceiraSeeder extends Seeder
{
    public function run(): void
    {
        $contratos = Contrato::withoutGlobalScopes()
            ->whereIn('status', ['vigente', 'vencido'])
            ->get();

        if ($contratos->isEmpty()) {
            return;
        }

        $user = User::first();
        if (! $user) {
            return;
        }

        foreach ($contratos as $contrato) {
            $this->criarExecucoesParaContrato($contrato, $user->id);
        }
    }

    private function criarExecucoesParaContrato(Contrato $contrato, int $userId): void
    {
        $inicio = $contrato->data_inicio;
        $fim = min($contrato->data_fim, now());
        $valorMensal = $contrato->valor_mensal ?? round($contrato->valor_global / max($contrato->prazo_meses, 1), 2);

        // Calcula meses executados
        $mesesExecutados = $inicio->diffInMonths($fim);
        $mesesExecutados = min($mesesExecutados, 6); // Limita a 6 meses para não gerar excesso

        if ($mesesExecutados < 1) {
            return;
        }

        // Empenho inicial
        ExecucaoFinanceira::create([
            'contrato_id'        => $contrato->id,
            'tipo_execucao'      => 'empenho_adicional',
            'descricao'          => "Empenho inicial do contrato {$contrato->numero}",
            'valor'              => $valorMensal * min($mesesExecutados, 3),
            'data_execucao'      => $inicio->copy()->subDays(5)->format('Y-m-d'),
            'numero_empenho'     => $this->gerarNumeroEmpenho($contrato, 0),
            'competencia'        => $inicio->format('Y-m'),
            'observacoes'        => 'Empenho registrado no início da vigência contratual.',
            'registrado_por'     => $userId,
        ]);

        // Liquidações e pagamentos mensais
        for ($i = 0; $i < $mesesExecutados; $i++) {
            $mesRef = $inicio->copy()->addMonths($i);
            $competencia = $mesRef->format('Y-m');
            $valorParcela = $this->variarValor($valorMensal);

            // Liquidação
            ExecucaoFinanceira::create([
                'contrato_id'        => $contrato->id,
                'tipo_execucao'      => 'liquidacao',
                'descricao'          => "Liquidação ref. {$competencia} — {$contrato->numero}",
                'valor'              => $valorParcela,
                'data_execucao'      => $mesRef->copy()->endOfMonth()->subDays(5)->format('Y-m-d'),
                'numero_nota_fiscal' => $this->gerarNumeroNF($contrato, $i),
                'numero_empenho'     => $this->gerarNumeroEmpenho($contrato, 0),
                'competencia'        => $competencia,
                'registrado_por'     => $userId,
            ]);

            // Pagamento (com atraso de alguns dias após liquidação)
            ExecucaoFinanceira::create([
                'contrato_id'        => $contrato->id,
                'tipo_execucao'      => 'pagamento',
                'descricao'          => "Pagamento ref. {$competencia} — {$contrato->numero}",
                'valor'              => $valorParcela,
                'data_execucao'      => $mesRef->copy()->endOfMonth()->format('Y-m-d'),
                'numero_nota_fiscal' => $this->gerarNumeroNF($contrato, $i),
                'numero_empenho'     => $this->gerarNumeroEmpenho($contrato, 0),
                'competencia'        => $competencia,
                'registrado_por'     => $userId,
            ]);
        }

        // Empenho adicional para contratos com mais de 3 meses
        if ($mesesExecutados > 3) {
            ExecucaoFinanceira::create([
                'contrato_id'        => $contrato->id,
                'tipo_execucao'      => 'empenho_adicional',
                'descricao'          => "Empenho complementar — {$contrato->numero}",
                'valor'              => $valorMensal * 3,
                'data_execucao'      => $inicio->copy()->addMonths(3)->format('Y-m-d'),
                'numero_empenho'     => $this->gerarNumeroEmpenho($contrato, 1),
                'competencia'        => $inicio->copy()->addMonths(3)->format('Y-m'),
                'observacoes'        => 'Reforço de empenho para continuidade da execução contratual.',
                'registrado_por'     => $userId,
            ]);
        }
    }

    private function variarValor(float $valorBase): float
    {
        // Variação de ±2% para simular diferenças reais
        $fator = 1 + (rand(-200, 200) / 10000);

        return round($valorBase * $fator, 2);
    }

    private function gerarNumeroNF(Contrato $contrato, int $sequencia): string
    {
        $base = crc32($contrato->numero) % 90000 + 10000;

        return (string) ($base + $sequencia);
    }

    private function gerarNumeroEmpenho(Contrato $contrato, int $sequencia): string
    {
        $ano = $contrato->ano;
        $base = str_pad((crc32($contrato->numero) % 9000) + 1000, 4, '0', STR_PAD_LEFT);

        return "{$ano}NE{$base}" . ($sequencia > 0 ? "-R{$sequencia}" : '');
    }
}
