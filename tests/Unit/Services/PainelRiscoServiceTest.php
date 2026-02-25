<?php

namespace Tests\Unit\Services;

use App\Models\Contrato;
use App\Services\PainelRiscoService;
use Tests\TestCase;
use Tests\Traits\RunsTenantMigrations;
use Tests\Traits\SeedsTenantData;

class PainelRiscoServiceTest extends TestCase
{
    use RunsTenantMigrations;
    use SeedsTenantData;

    protected PainelRiscoService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpTenant();
        $this->seedBaseData();
        $this->service = new PainelRiscoService();
    }

    public function test_indicadores_5_cards(): void
    {
        Contrato::factory()->vigente()->create();
        Contrato::factory()->vigente()->altoRisco()->create();

        $indicadores = $this->service->indicadores();

        $this->assertArrayHasKey('total_ativos', $indicadores);
        $this->assertArrayHasKey('pct_alto_risco', $indicadores);
        $this->assertArrayHasKey('vencendo_30d', $indicadores);
        $this->assertArrayHasKey('aditivos_acima_20', $indicadores);
        $this->assertArrayHasKey('sem_doc_obrigatoria', $indicadores);
    }

    public function test_ranking_risco_ordenado_por_score(): void
    {
        Contrato::factory()->vigente()->create(['score_risco' => 10]);
        Contrato::factory()->vigente()->altoRisco()->create(['score_risco' => 80]);

        $ranking = $this->service->rankingRisco();

        $this->assertGreaterThanOrEqual(2, $ranking->total());
        // Primeiro item deve ter score >= segundo
        if ($ranking->count() >= 2) {
            $this->assertGreaterThanOrEqual(
                $ranking->items()[1]->score_risco,
                $ranking->items()[0]->score_risco
            );
        }
    }

    public function test_mapa_risco_por_secretaria(): void
    {
        Contrato::factory()->vigente()->create();

        $mapa = $this->service->mapaRiscoPorSecretaria();

        $this->assertIsArray($mapa);
        $this->assertNotEmpty($mapa);
        // mapaRiscoPorSecretaria returns Secretaria models as arrays
        $this->assertArrayHasKey('nome', $mapa[0]);
        $this->assertArrayHasKey('total_contratos', $mapa[0]);
    }

    public function test_dados_relatorio_tce(): void
    {
        Contrato::factory()->vigente()->create();

        $dados = $this->service->dadosRelatorioTCE();

        $this->assertArrayHasKey('resumo', $dados);
        $this->assertArrayHasKey('contratos', $dados);
        $this->assertArrayHasKey('municipio', $dados);
        $this->assertArrayHasKey('data_geracao', $dados);
    }

    public function test_dados_relatorio_tce_resumo_contadores(): void
    {
        Contrato::factory()->vigente()->create();

        $dados = $this->service->dadosRelatorioTCE();

        $resumo = $dados['resumo'];
        $this->assertArrayHasKey('total_monitorados', $resumo);
        $this->assertArrayHasKey('alto_risco', $resumo);
        $this->assertArrayHasKey('medio_risco', $resumo);
        $this->assertArrayHasKey('baixo_risco', $resumo);

        // Contadores devem somar ao total
        $soma = $resumo['alto_risco'] + $resumo['medio_risco'] + $resumo['baixo_risco'];
        $this->assertEquals($resumo['total_monitorados'], $soma);
        $this->assertGreaterThanOrEqual(1, $resumo['total_monitorados']);
    }

    public function test_label_categoria(): void
    {
        $this->assertEquals('Vencimento', $this->service->labelCategoria('vencimento'));
        $this->assertEquals('Financeiro', $this->service->labelCategoria('financeiro'));
        $this->assertEquals('Documental', $this->service->labelCategoria('documental'));
    }

    public function test_cor_categoria(): void
    {
        $cor = $this->service->corCategoria('vencimento');

        $this->assertNotEmpty($cor);
        $this->assertIsString($cor);
    }
}
