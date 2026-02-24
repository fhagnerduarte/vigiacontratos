<?php

namespace Tests\Feature\Performance;

use App\Models\Contrato;
use App\Models\User;
use App\Services\PainelRiscoService;
use Tests\TestCase;
use Tests\Traits\RunsTenantMigrations;
use Tests\Traits\SeedsTenantData;

class PainelRiscoPerformanceTest extends TestCase
{
    use RunsTenantMigrations;
    use SeedsTenantData;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpTenant();
        $this->seedBaseData();
        $this->admin = $this->createAdminUser([
            'mfa_secret' => 'TESTSECRETKEY123',
            'mfa_enabled_at' => now(),
            'mfa_recovery_codes' => encrypt(json_encode(['AAAA-BBBB'])),
        ]);
    }

    protected function actAsAdmin(): self
    {
        return $this->actingAs($this->admin)->withSession(['mfa_verified' => true]);
    }

    private function medirTempo(callable $fn): float
    {
        $inicio = microtime(true);
        $fn();

        return microtime(true) - $inicio;
    }

    // ─── PERFORMANCE: PAINEL DE RISCO < 2 SEGUNDOS ───────────

    public function test_painel_risco_carrega_em_menos_de_2_segundos_sem_contratos(): void
    {
        $duracao = $this->medirTempo(function () {
            $response = $this->actAsAdmin()->get(route('tenant.painel-risco.index'));
            $response->assertStatus(200);
        });

        $this->assertLessThan(2.0, $duracao, 'Painel de Risco sem contratos deve responder em <2s');
    }

    public function test_painel_risco_carrega_em_menos_de_2_segundos_com_contratos_vigentes(): void
    {
        Contrato::factory()->vigente()->count(10)->create();

        $duracao = $this->medirTempo(function () {
            $response = $this->actAsAdmin()->get(route('tenant.painel-risco.index'));
            $response->assertStatus(200);
        });

        $this->assertLessThan(2.0, $duracao, 'Painel de Risco com 10 contratos deve responder em <2s');
    }

    public function test_painel_risco_carrega_em_menos_de_2_segundos_com_contratos_alto_risco(): void
    {
        Contrato::factory()->altoRisco()->count(5)->create();
        Contrato::factory()->vigente()->count(5)->create();

        $duracao = $this->medirTempo(function () {
            $response = $this->actAsAdmin()->get(route('tenant.painel-risco.index'));
            $response->assertStatus(200);
        });

        $this->assertLessThan(2.0, $duracao, 'Painel de Risco com contratos alto risco deve responder em <2s');
    }

    public function test_painel_risco_exportar_tce_em_menos_de_2_segundos(): void
    {
        Contrato::factory()->altoRisco()->count(3)->create();

        $duracao = $this->medirTempo(function () {
            $response = $this->actAsAdmin()->get(route('tenant.painel-risco.exportar-tce'));
            $response->assertStatus(200);
            $response->assertHeader('content-type', 'application/pdf');
        });

        $this->assertLessThan(2.0, $duracao, 'Exportar TCE deve gerar PDF em <2s');
    }

    public function test_indicadores_painel_calculados_em_menos_de_500ms(): void
    {
        Contrato::factory()->vigente()->count(10)->create();
        Contrato::factory()->altoRisco()->count(3)->create();

        $duracao = $this->medirTempo(function () {
            $indicadores = PainelRiscoService::indicadores();
            $this->assertArrayHasKey('total_ativos', $indicadores);
            $this->assertArrayHasKey('alto_risco', $indicadores);
        });

        $this->assertLessThan(0.5, $duracao, 'PainelRiscoService::indicadores() deve executar em <500ms');
    }
}
