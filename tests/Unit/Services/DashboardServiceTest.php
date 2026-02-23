<?php

namespace Tests\Unit\Services;

use App\Enums\NivelRisco;
use App\Enums\StatusContrato;
use App\Models\Contrato;
use App\Services\DashboardService;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;
use Tests\Traits\RunsTenantMigrations;
use Tests\Traits\SeedsTenantData;

class DashboardServiceTest extends TestCase
{
    use RunsTenantMigrations;
    use SeedsTenantData;

    protected DashboardService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpTenant();
        $this->seedBaseData();
        $this->service = new DashboardService();
    }

    public function test_indicadores_financeiros(): void
    {
        Contrato::factory()->vigente()->create(['valor_global' => 100000]);
        Contrato::factory()->vigente()->create(['valor_global' => 200000]);

        $indicadores = $this->service->indicadoresFinanceiros();

        $this->assertArrayHasKey('total_contratos_ativos', $indicadores);
        $this->assertArrayHasKey('valor_total_contratado', $indicadores);
        $this->assertGreaterThanOrEqual(2, $indicadores['total_contratos_ativos']);
    }

    public function test_mapa_risco(): void
    {
        Contrato::factory()->vigente()->create(['nivel_risco' => NivelRisco::Baixo]);
        Contrato::factory()->vigente()->altoRisco()->create();

        $mapa = $this->service->mapaRisco();

        $this->assertArrayHasKey('baixo', $mapa);
        $this->assertArrayHasKey('medio', $mapa);
        $this->assertArrayHasKey('alto', $mapa);
    }

    public function test_vencimentos_por_janela(): void
    {
        Contrato::factory()->vigente()->vencendoEm(15)->create();
        Contrato::factory()->vigente()->vencendoEm(45)->create();
        Contrato::factory()->vigente()->vencendoEm(100)->create();

        $janelas = $this->service->vencimentosPorJanela();

        $this->assertIsArray($janelas);
        $this->assertNotEmpty($janelas);
    }

    public function test_contratos_essenciais(): void
    {
        Contrato::factory()->vigente()->essencial()->vencendoEm(30)->create();

        $essenciais = $this->service->contratosEssenciais();

        $this->assertIsArray($essenciais);
    }

    public function test_score_gestao(): void
    {
        Contrato::factory()->vigente()->create();

        $score = $this->service->scoreGestao();

        $this->assertArrayHasKey('score', $score);
        $this->assertArrayHasKey('classificacao', $score);
        $this->assertArrayHasKey('cor', $score);
        $this->assertGreaterThanOrEqual(0, $score['score']);
        $this->assertLessThanOrEqual(100, $score['score']);
    }

    public function test_score_gestao_penaliza_vencidos(): void
    {
        // Cria contratos vigentes e vencidos (1 a 1 para evitar unique overflow)
        Contrato::factory()->vigente()->create();
        Contrato::factory()->vencido()->create();

        $score = $this->service->scoreGestao();

        // Com contratos vencidos, score deve ser menor que 100
        $this->assertLessThan(100, $score['score']);
    }

    public function test_tendencias_mensais(): void
    {
        Contrato::factory()->vigente()->create();

        $tendencias = $this->service->tendenciasMensais();

        $this->assertIsArray($tendencias);
    }

    public function test_ranking_fornecedores(): void
    {
        Contrato::factory()->vigente()->create();

        $ranking = $this->service->rankingFornecedores();

        $this->assertIsArray($ranking);
    }

    public function test_agregar_salva_no_banco(): void
    {
        Contrato::factory()->vigente()->create();

        $agregado = $this->service->agregar();

        $this->assertNotNull($agregado->id);
        $this->assertNotNull($agregado->dados_completos);
    }
}
