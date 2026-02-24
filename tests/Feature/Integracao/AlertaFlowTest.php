<?php

namespace Tests\Feature\Integracao;

use App\Enums\PrioridadeAlerta;
use App\Enums\StatusAlerta;
use App\Enums\StatusContrato;
use App\Jobs\ProcessarAlertaJob;
use App\Models\Alerta;
use App\Models\ConfiguracaoAlerta;
use App\Models\Contrato;
use App\Models\User;
use App\Notifications\AlertaVencimentoNotification;
use App\Services\AlertaService;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;
use Tests\Traits\RunsTenantMigrations;
use Tests\Traits\SeedsTenantData;

class AlertaFlowTest extends TestCase
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

        ConfiguracaoAlerta::firstOrCreate(
            ['dias_antecedencia' => 30],
            ['prioridade_padrao' => PrioridadeAlerta::Atencao, 'is_ativo' => true]
        );
    }

    protected function actAsAdmin(): self
    {
        return $this->actingAs($this->admin)->withSession(['mfa_verified' => true]);
    }

    // ─── FLUXO 1: VERIFICAR VENCIMENTOS GERA ALERTA E DESPACHA JOB ──

    public function test_fluxo_verificar_vencimentos_gera_alerta_e_despacha_job(): void
    {
        Queue::fake();

        $contrato = Contrato::factory()->create([
            'status' => StatusContrato::Vigente,
            'data_fim' => now()->addDays(25)->format('Y-m-d'),
        ]);

        $resultado = AlertaService::verificarVencimentos();

        $this->assertGreaterThanOrEqual(1, $resultado['alertas_gerados']);
        $this->assertDatabaseHas('alertas', [
            'contrato_id' => $contrato->id,
            'status' => StatusAlerta::Pendente->value,
        ], 'tenant');
    }

    // ─── FLUXO 2: ALERTA NAO DUPLICADO ─────────────────────────

    public function test_fluxo_alerta_nao_duplicado_em_segunda_verificacao(): void
    {
        Queue::fake();

        Contrato::factory()->create([
            'status' => StatusContrato::Vigente,
            'data_fim' => now()->addDays(25)->format('Y-m-d'),
        ]);

        $resultado1 = AlertaService::verificarVencimentos();
        $resultado2 = AlertaService::verificarVencimentos();

        $this->assertGreaterThanOrEqual(1, $resultado1['alertas_gerados']);
        $this->assertEquals(0, $resultado2['alertas_gerados']);
    }

    // ─── FLUXO 3: PROCESSAR ALERTA JOB ENVIA NOTIFICACAO E LOG ──

    public function test_fluxo_processar_alerta_job_envia_notificacao_e_registra_log(): void
    {
        Notification::fake();

        $alerta = Alerta::factory()->create([
            'status' => StatusAlerta::Pendente,
        ]);

        $destinatarios = [
            ['email' => $this->admin->email, 'user' => $this->admin],
        ];
        $tenantDb = config('database.connections.tenant.database');

        $job = new ProcessarAlertaJob($alerta, $destinatarios, $tenantDb);
        $job->handle();

        Notification::assertSentTo($this->admin, AlertaVencimentoNotification::class);

        $alerta->refresh();
        $this->assertEquals(StatusAlerta::Enviado, $alerta->status);

        $this->assertDatabaseHas('log_notificacoes', [
            'alerta_id' => $alerta->id,
            'sucesso' => true,
        ], 'tenant');
    }

    // ─── FLUXO 4: RESOLVER ALERTA MANUALMENTE VIA HTTP ─────────

    public function test_fluxo_resolver_alerta_manualmente_via_http(): void
    {
        $alerta = Alerta::factory()->create([
            'status' => StatusAlerta::Pendente,
        ]);

        $response = $this->actAsAdmin()->post(route('tenant.alertas.resolver', $alerta), [
            'observacao' => 'Contrato renovado com sucesso pelo setor responsavel.',
        ]);

        $response->assertRedirect();

        $alerta->refresh();
        $this->assertEquals(StatusAlerta::Resolvido, $alerta->status);
        $this->assertEquals($this->admin->id, $alerta->resolvido_por);
    }

    // ─── FLUXO 5: ALERTA URGENTE CONTRATO ESSENCIAL ────────────

    public function test_fluxo_alerta_urgente_contrato_essencial(): void
    {
        Queue::fake();

        // Configuracao para 7 dias (Urgente)
        ConfiguracaoAlerta::firstOrCreate(
            ['dias_antecedencia' => 7],
            ['prioridade_padrao' => PrioridadeAlerta::Urgente, 'is_ativo' => true]
        );

        Contrato::factory()->essencial()->create([
            'status' => StatusContrato::Vigente,
            'data_fim' => now()->addDays(5)->format('Y-m-d'),
        ]);

        AlertaService::verificarVencimentos();

        // Contrato essencial <=7 dias: prioridade deve ser Urgente (RN-051 eleva atencao->urgente)
        $alerta = Alerta::orderByDesc('id')->first();
        $this->assertNotNull($alerta);
        $this->assertEquals(PrioridadeAlerta::Urgente, $alerta->prioridade);
    }

    // ─── FLUXO 6: CONTRATO VENCIDO MARCADO NO MOTOR ────────────

    public function test_fluxo_contrato_vencido_marcado_no_motor(): void
    {
        $contrato = Contrato::factory()->create([
            'status' => StatusContrato::Vigente,
            'data_fim' => now()->subDays(3)->format('Y-m-d'),
        ]);

        $resultado = AlertaService::verificarVencimentos();

        $this->assertGreaterThanOrEqual(1, $resultado['contratos_vencidos']);

        $contrato->refresh();
        $this->assertEquals(StatusContrato::Vencido, $contrato->status);
        $this->assertTrue($contrato->is_irregular);
    }
}
