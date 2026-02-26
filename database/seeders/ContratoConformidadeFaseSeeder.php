<?php

namespace Database\Seeders;

use App\Enums\FaseContratual;
use App\Models\Contrato;
use App\Models\ContratoConformidadeFase;
use Illuminate\Database\Seeder;

class ContratoConformidadeFaseSeeder extends Seeder
{
    public function run(): void
    {
        $contratos = Contrato::withoutGlobalScopes()->get();

        if ($contratos->isEmpty()) {
            return;
        }

        foreach ($contratos as $contrato) {
            $perfil = $this->getPerfilConformidade($contrato);

            foreach (FaseContratual::cases() as $fase) {
                $existing = ContratoConformidadeFase::where('contrato_id', $contrato->id)
                    ->where('fase', $fase->value)
                    ->exists();

                if ($existing) {
                    continue;
                }

                $dados = $perfil[$fase->value] ?? $this->getFasePadrao();

                ContratoConformidadeFase::create([
                    'contrato_id'             => $contrato->id,
                    'fase'                    => $fase->value,
                    'percentual_conformidade' => $dados['percentual'],
                    'total_obrigatorios'      => $dados['obrigatorios'],
                    'total_presentes'         => $dados['presentes'],
                    'nivel_semaforo'          => $this->calcularSemaforo($dados['percentual']),
                ]);
            }
        }
    }

    private function getPerfilConformidade(Contrato $contrato): array
    {
        // Contratos encerrados com 100% → tudo verde
        if ($contrato->status === 'encerrado') {
            return $this->perfilCompleto();
        }

        // Contratos cancelados → fases iniciais OK, execução vermelha
        if ($contrato->status === 'cancelado') {
            return $this->perfilCancelado();
        }

        // Contratos suspensos → fases iniciais OK, execução parcial
        if ($contrato->status === 'suspenso') {
            return $this->perfilSuspenso();
        }

        // Contratos vencidos 100% executados → quase tudo verde
        if ($contrato->status === 'vencido' && $contrato->percentual_executado >= 100) {
            return $this->perfilVencidoCompleto();
        }

        // Contratos com documentação incompleta (008, 013)
        if (in_array($contrato->numero, ['008/2026', '013/2026'])) {
            return $this->perfilIncompleto();
        }

        // Contratos de alto risco → conformidade parcial
        if ($contrato->score_risco >= 50) {
            return $this->perfilAltoRisco();
        }

        // Contratos vigentes normais
        return $this->perfilNormal();
    }

    private function perfilCompleto(): array
    {
        return [
            'planejamento'       => ['percentual' => 100, 'obrigatorios' => 5, 'presentes' => 5],
            'formalizacao'       => ['percentual' => 100, 'obrigatorios' => 6, 'presentes' => 6],
            'publicacao'         => ['percentual' => 100, 'obrigatorios' => 3, 'presentes' => 3],
            'fiscalizacao'       => ['percentual' => 100, 'obrigatorios' => 4, 'presentes' => 4],
            'execucao_financeira' => ['percentual' => 100, 'obrigatorios' => 5, 'presentes' => 5],
            'gestao_aditivos'    => ['percentual' => 100, 'obrigatorios' => 2, 'presentes' => 2],
            'encerramento'       => ['percentual' => 100, 'obrigatorios' => 4, 'presentes' => 4],
        ];
    }

    private function perfilNormal(): array
    {
        return [
            'planejamento'       => ['percentual' => 100, 'obrigatorios' => 5, 'presentes' => 5],
            'formalizacao'       => ['percentual' => 100, 'obrigatorios' => 6, 'presentes' => 6],
            'publicacao'         => ['percentual' => 100, 'obrigatorios' => 3, 'presentes' => 3],
            'fiscalizacao'       => ['percentual' => 75,  'obrigatorios' => 4, 'presentes' => 3],
            'execucao_financeira' => ['percentual' => 80, 'obrigatorios' => 5, 'presentes' => 4],
            'gestao_aditivos'    => ['percentual' => 100, 'obrigatorios' => 2, 'presentes' => 2],
            'encerramento'       => ['percentual' => 0,   'obrigatorios' => 4, 'presentes' => 0],
        ];
    }

    private function perfilAltoRisco(): array
    {
        return [
            'planejamento'       => ['percentual' => 100, 'obrigatorios' => 5, 'presentes' => 5],
            'formalizacao'       => ['percentual' => 83,  'obrigatorios' => 6, 'presentes' => 5],
            'publicacao'         => ['percentual' => 67,  'obrigatorios' => 3, 'presentes' => 2],
            'fiscalizacao'       => ['percentual' => 50,  'obrigatorios' => 4, 'presentes' => 2],
            'execucao_financeira' => ['percentual' => 60, 'obrigatorios' => 5, 'presentes' => 3],
            'gestao_aditivos'    => ['percentual' => 50,  'obrigatorios' => 2, 'presentes' => 1],
            'encerramento'       => ['percentual' => 0,   'obrigatorios' => 4, 'presentes' => 0],
        ];
    }

    private function perfilIncompleto(): array
    {
        return [
            'planejamento'       => ['percentual' => 80,  'obrigatorios' => 5, 'presentes' => 4],
            'formalizacao'       => ['percentual' => 50,  'obrigatorios' => 6, 'presentes' => 3],
            'publicacao'         => ['percentual' => 33,  'obrigatorios' => 3, 'presentes' => 1],
            'fiscalizacao'       => ['percentual' => 25,  'obrigatorios' => 4, 'presentes' => 1],
            'execucao_financeira' => ['percentual' => 40, 'obrigatorios' => 5, 'presentes' => 2],
            'gestao_aditivos'    => ['percentual' => 0,   'obrigatorios' => 2, 'presentes' => 0],
            'encerramento'       => ['percentual' => 0,   'obrigatorios' => 4, 'presentes' => 0],
        ];
    }

    private function perfilCancelado(): array
    {
        return [
            'planejamento'       => ['percentual' => 100, 'obrigatorios' => 5, 'presentes' => 5],
            'formalizacao'       => ['percentual' => 100, 'obrigatorios' => 6, 'presentes' => 6],
            'publicacao'         => ['percentual' => 100, 'obrigatorios' => 3, 'presentes' => 3],
            'fiscalizacao'       => ['percentual' => 50,  'obrigatorios' => 4, 'presentes' => 2],
            'execucao_financeira' => ['percentual' => 20, 'obrigatorios' => 5, 'presentes' => 1],
            'gestao_aditivos'    => ['percentual' => 0,   'obrigatorios' => 2, 'presentes' => 0],
            'encerramento'       => ['percentual' => 25,  'obrigatorios' => 4, 'presentes' => 1],
        ];
    }

    private function perfilSuspenso(): array
    {
        return [
            'planejamento'       => ['percentual' => 100, 'obrigatorios' => 5, 'presentes' => 5],
            'formalizacao'       => ['percentual' => 100, 'obrigatorios' => 6, 'presentes' => 6],
            'publicacao'         => ['percentual' => 100, 'obrigatorios' => 3, 'presentes' => 3],
            'fiscalizacao'       => ['percentual' => 75,  'obrigatorios' => 4, 'presentes' => 3],
            'execucao_financeira' => ['percentual' => 40, 'obrigatorios' => 5, 'presentes' => 2],
            'gestao_aditivos'    => ['percentual' => 0,   'obrigatorios' => 2, 'presentes' => 0],
            'encerramento'       => ['percentual' => 0,   'obrigatorios' => 4, 'presentes' => 0],
        ];
    }

    private function perfilVencidoCompleto(): array
    {
        return [
            'planejamento'       => ['percentual' => 100, 'obrigatorios' => 5, 'presentes' => 5],
            'formalizacao'       => ['percentual' => 100, 'obrigatorios' => 6, 'presentes' => 6],
            'publicacao'         => ['percentual' => 100, 'obrigatorios' => 3, 'presentes' => 3],
            'fiscalizacao'       => ['percentual' => 100, 'obrigatorios' => 4, 'presentes' => 4],
            'execucao_financeira' => ['percentual' => 100, 'obrigatorios' => 5, 'presentes' => 5],
            'gestao_aditivos'    => ['percentual' => 100, 'obrigatorios' => 2, 'presentes' => 2],
            'encerramento'       => ['percentual' => 75,  'obrigatorios' => 4, 'presentes' => 3],
        ];
    }

    private function getFasePadrao(): array
    {
        return ['percentual' => 50, 'obrigatorios' => 4, 'presentes' => 2];
    }

    private function calcularSemaforo(float $percentual): string
    {
        if ($percentual >= 80) {
            return 'verde';
        }
        if ($percentual >= 50) {
            return 'amarelo';
        }

        return 'vermelho';
    }
}
