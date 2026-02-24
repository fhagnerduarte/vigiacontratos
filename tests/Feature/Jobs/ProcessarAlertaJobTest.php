<?php

namespace Tests\Feature\Jobs;

use App\Enums\StatusAlerta;
use App\Jobs\ProcessarAlertaJob;
use App\Models\Alerta;
use App\Models\User;
use App\Notifications\AlertaVencimentoNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;
use Tests\Traits\RunsTenantMigrations;
use Tests\Traits\SeedsTenantData;

class ProcessarAlertaJobTest extends TestCase
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

    // ─── PROPRIEDADES DO JOB ─────────────────────────────────

    public function test_job_propriedades_configuradas(): void
    {
        $alerta = Alerta::factory()->create();
        $destinatarios = [['email' => 'test@example.com']];
        $tenantDb = config('database.connections.tenant.database');

        $job = new ProcessarAlertaJob($alerta, $destinatarios, $tenantDb);

        $this->assertEquals(3, $job->tries);
        $this->assertEquals([60, 300, 900], $job->backoff());
        $this->assertEquals('alertas', $job->queue);
    }

    public function test_job_dispatch_na_fila_alertas(): void
    {
        Queue::fake();

        $alerta = Alerta::factory()->create();
        $destinatarios = [['email' => 'test@example.com']];
        $tenantDb = config('database.connections.tenant.database');

        ProcessarAlertaJob::dispatch($alerta, $destinatarios, $tenantDb);

        Queue::assertPushedOn('alertas', ProcessarAlertaJob::class);
    }

    // ─── ENVIO DE NOTIFICACOES ───────────────────────────────

    public function test_job_envia_notificacao_para_usuario(): void
    {
        Notification::fake();

        $alerta = Alerta::factory()->create();
        $destinatarios = [
            ['email' => $this->admin->email, 'user' => $this->admin],
        ];
        $tenantDb = config('database.connections.tenant.database');

        $job = new ProcessarAlertaJob($alerta, $destinatarios, $tenantDb);
        $job->handle();

        Notification::assertSentTo($this->admin, AlertaVencimentoNotification::class);
    }

    public function test_job_envia_email_on_demand(): void
    {
        Notification::fake();

        $alerta = Alerta::factory()->create();
        $destinatarios = [
            ['email' => 'externo@prefeitura.gov.br'],
        ];
        $tenantDb = config('database.connections.tenant.database');

        $job = new ProcessarAlertaJob($alerta, $destinatarios, $tenantDb);
        $job->handle();

        Notification::assertSentOnDemand(AlertaVencimentoNotification::class);
    }

    public function test_job_nao_envia_se_alerta_resolvido(): void
    {
        Notification::fake();

        $alerta = Alerta::factory()->resolvido()->create();
        $destinatarios = [
            ['email' => $this->admin->email, 'user' => $this->admin],
        ];
        $tenantDb = config('database.connections.tenant.database');

        $job = new ProcessarAlertaJob($alerta, $destinatarios, $tenantDb);
        $job->handle();

        Notification::assertNothingSent();
    }

    // ─── LOG DE NOTIFICACOES ─────────────────────────────────

    public function test_job_registra_log_notificacao_sucesso(): void
    {
        Notification::fake();

        $alerta = Alerta::factory()->create();
        $destinatarios = [
            ['email' => $this->admin->email, 'user' => $this->admin],
        ];
        $tenantDb = config('database.connections.tenant.database');

        $job = new ProcessarAlertaJob($alerta, $destinatarios, $tenantDb);
        $job->handle();

        $this->assertDatabaseHas('log_notificacoes', [
            'alerta_id' => $alerta->id,
            'sucesso' => true,
        ], 'tenant');
    }

    public function test_job_atualiza_status_alerta(): void
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

        $alerta->refresh();
        $this->assertEquals(StatusAlerta::Enviado, $alerta->status);
    }

    public function test_job_multiplos_destinatarios_registra_logs(): void
    {
        Notification::fake();

        $alerta = Alerta::factory()->create();
        $user2 = $this->createUserWithRole('secretario');
        $destinatarios = [
            ['email' => $this->admin->email, 'user' => $this->admin],
            ['email' => $user2->email, 'user' => $user2],
            ['email' => 'externo@example.com'],
        ];
        $tenantDb = config('database.connections.tenant.database');

        $job = new ProcessarAlertaJob($alerta, $destinatarios, $tenantDb);
        $job->handle();

        $totalLogs = $alerta->logNotificacoes()->count();
        // Admin: sistema + email = 2 logs; user2: sistema + email = 2 logs; externo: email = 1 log
        $this->assertGreaterThanOrEqual(3, $totalLogs);
    }
}
