<?php

namespace Tests\Feature\Relatorios;

use App\Models\Contrato;
use App\Models\Documento;
use App\Models\Role;
use App\Models\User;
use Tests\TestCase;
use Tests\Traits\RunsTenantMigrations;
use Tests\Traits\SeedsTenantData;

class DocumentosRelatorioTest extends TestCase
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

    // ─── RELATORIO DOCUMENTOS CONTRATO ──────────────────────

    public function test_relatorio_documentos_contrato_retorna_pdf(): void
    {
        $contrato = Contrato::factory()->create();
        Documento::factory()->create([
            'documentable_type' => Contrato::class,
            'documentable_id' => $contrato->id,
            'uploaded_by' => $this->admin->id,
        ]);

        $response = $this->actAsAdmin()->get(
            route('tenant.relatorios.documentos-contrato', $contrato)
        );

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_relatorio_documentos_exige_permissao(): void
    {
        $contrato = Contrato::factory()->create();
        $role = Role::factory()->create(['nome' => 'role_sem_download']);
        $user = User::factory()->create(['role_id' => $role->id]);

        $response = $this->actingAs($user)->get(
            route('tenant.relatorios.documentos-contrato', $contrato)
        );

        // Middleware permission retorna 403; SecretariaScope pode retornar 404
        $this->assertTrue(
            in_array($response->getStatusCode(), [403, 404]),
            'Esperado 403 ou 404, recebido: ' . $response->getStatusCode()
        );
    }

    public function test_relatorio_documentos_contrato_sem_documentos_funciona(): void
    {
        $contrato = Contrato::factory()->create();

        $response = $this->actAsAdmin()->get(
            route('tenant.relatorios.documentos-contrato', $contrato)
        );

        $response->assertStatus(200);
    }
}
