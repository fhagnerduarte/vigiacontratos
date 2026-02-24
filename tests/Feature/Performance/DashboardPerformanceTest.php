<?php

namespace Tests\Feature\Performance;

use App\Models\Contrato;
use App\Models\Secretaria;
use App\Models\User;
use App\Services\DashboardService;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;
use Tests\Traits\RunsTenantMigrations;
use Tests\Traits\SeedsTenantData;

class DashboardPerformanceTest extends TestCase
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

    private function cacheKey(): string
    {
        return 'dashboard:' . config('database.connections.tenant.database');
    }

    // ─── PERFORMANCE: DASHBOARD < 2 SEGUNDOS ─────────────────

    public function test_dashboard_carrega_em_menos_de_2_segundos_sem_contratos(): void
    {
        $duracao = $this->medirTempo(function () {
            $response = $this->actAsAdmin()->get(route('tenant.dashboard'));
            $response->assertStatus(200);
        });

        $this->assertLessThan(2.0, $duracao, 'Dashboard sem contratos deve responder em <2s');
    }

    public function test_dashboard_carrega_em_menos_de_2_segundos_com_dados_basicos(): void
    {
        Contrato::factory()->vigente()->count(10)->create();

        $duracao = $this->medirTempo(function () {
            $response = $this->actAsAdmin()->get(route('tenant.dashboard'));
            $response->assertStatus(200);
        });

        $this->assertLessThan(2.0, $duracao, 'Dashboard com 10 contratos deve responder em <2s');
    }

    public function test_dashboard_carrega_em_menos_de_2_segundos_com_cache_populado(): void
    {
        Contrato::factory()->vigente()->count(5)->create();
        DashboardService::agregar();

        $duracao = $this->medirTempo(function () {
            $response = $this->actAsAdmin()->get(route('tenant.dashboard'));
            $response->assertStatus(200);
        });

        $this->assertLessThan(2.0, $duracao, 'Dashboard com cache populado deve responder em <2s');
    }

    public function test_dashboard_carrega_em_menos_de_2_segundos_com_filtros(): void
    {
        $secretaria = Secretaria::factory()->create();
        Contrato::factory()->vigente()->count(5)->create(['secretaria_id' => $secretaria->id]);

        $duracao = $this->medirTempo(function () use ($secretaria) {
            $response = $this->actAsAdmin()->get(route('tenant.dashboard', [
                'secretaria_id' => $secretaria->id,
            ]));
            $response->assertStatus(200);
        });

        $this->assertLessThan(2.0, $duracao, 'Dashboard com filtro secretaria deve responder em <2s');
    }

    public function test_dashboard_atualizar_executa_em_menos_de_2_segundos(): void
    {
        Contrato::factory()->vigente()->count(5)->create();

        $duracao = $this->medirTempo(function () {
            $response = $this->actAsAdmin()->post(route('tenant.dashboard.atualizar'));
            $response->assertRedirect(route('tenant.dashboard'));
        });

        $this->assertLessThan(2.0, $duracao, 'Dashboard atualizar deve executar em <2s');
    }

    public function test_dashboard_com_cache_invalido_recalcula_em_menos_de_2_segundos(): void
    {
        Contrato::factory()->vigente()->count(5)->create();
        DashboardService::agregar();
        Cache::forget($this->cacheKey());

        $duracao = $this->medirTempo(function () {
            $response = $this->actAsAdmin()->get(route('tenant.dashboard'));
            $response->assertStatus(200);
        });

        $this->assertLessThan(2.0, $duracao, 'Dashboard com cache invalido deve recalcular em <2s');
    }

    // ─── CACHE REDIS: COMPORTAMENTO ──────────────────────────

    public function test_cache_miss_calcula_dados_em_tempo_real(): void
    {
        Cache::forget($this->cacheKey());

        $response = $this->actAsAdmin()->get(route('tenant.dashboard'));

        $response->assertStatus(200);
        $response->assertViewHas('dados');
    }

    public function test_cache_populado_por_dashboard_agregar(): void
    {
        Cache::forget($this->cacheKey());
        $this->assertFalse(Cache::has($this->cacheKey()));

        DashboardService::agregar();

        // agregar() faz Cache::forget — o cache sera recriado no proximo obterDadosCacheados()
        // Chamamos obterDadosCacheados() para popular o cache via Cache::remember
        DashboardService::obterDadosCacheados();

        $this->assertTrue(Cache::has($this->cacheKey()));
    }

    public function test_cache_invalidado_por_dashboard_atualizar(): void
    {
        // Popular cache via obterDadosCacheados
        DashboardService::obterDadosCacheados();
        $this->assertTrue(Cache::has($this->cacheKey()));

        // POST atualizar chama agregar() que faz Cache::forget
        $this->actAsAdmin()->post(route('tenant.dashboard.atualizar'));

        // O cache foi invalidado pelo agregar(), mas sera recriado na proxima leitura
        // Vamos verificar que ao acessar novamente o dashboard, o cache e recriado
        $this->actAsAdmin()->get(route('tenant.dashboard'));
        $this->assertTrue(Cache::has($this->cacheKey()));
    }

    public function test_cache_invalido_recria_automaticamente(): void
    {
        DashboardService::obterDadosCacheados();
        $this->assertTrue(Cache::has($this->cacheKey()));

        Cache::forget($this->cacheKey());
        $this->assertFalse(Cache::has($this->cacheKey()));

        // GET no dashboard aciona Cache::remember que recria o cache
        $this->actAsAdmin()->get(route('tenant.dashboard'));

        $this->assertTrue(Cache::has($this->cacheKey()));
    }
}
