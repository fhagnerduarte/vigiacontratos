<?php

namespace Tests\Feature\Controllers;

use App\Models\Contrato;
use App\Models\Fiscal;
use App\Models\Role;
use App\Models\Secretaria;
use App\Models\Servidor;
use App\Models\User;
use Tests\TestCase;
use Tests\Traits\RunsTenantMigrations;
use Tests\Traits\SeedsTenantData;

class FiscaisControllerTest extends TestCase
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

    // ─── STORE ──────────────────────────────────────────────

    public function test_designa_fiscal_primeira_vez_com_sucesso(): void
    {
        $secretaria = Secretaria::factory()->create();
        $contrato = Contrato::factory()->create(['secretaria_id' => $secretaria->id]);
        $servidor = Servidor::factory()->create(['secretaria_id' => $secretaria->id]);

        $response = $this->actAsAdmin()->post(
            route('tenant.contratos.fiscais.store', $contrato),
            ['servidor_id' => $servidor->id]
        );

        $response->assertRedirect(route('tenant.contratos.show', $contrato));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('fiscais', [
            'contrato_id' => $contrato->id,
            'servidor_id' => $servidor->id,
            'is_atual' => true,
        ], 'tenant');
    }

    public function test_troca_fiscal_existente_mantem_historico(): void
    {
        $secretaria = Secretaria::factory()->create();
        $contrato = Contrato::factory()->create(['secretaria_id' => $secretaria->id]);
        $servidorAnterior = Servidor::factory()->create(['secretaria_id' => $secretaria->id]);
        $servidorNovo = Servidor::factory()->create(['secretaria_id' => $secretaria->id]);

        // Designar primeiro fiscal
        Fiscal::create([
            'contrato_id' => $contrato->id,
            'servidor_id' => $servidorAnterior->id,
            'nome' => $servidorAnterior->nome,
            'matricula' => $servidorAnterior->matricula,
            'cargo' => $servidorAnterior->cargo,
            'email' => $servidorAnterior->email,
            'data_inicio' => now()->subMonth()->toDateString(),
            'is_atual' => true,
        ]);

        // Trocar fiscal
        $response = $this->actAsAdmin()->post(
            route('tenant.contratos.fiscais.store', $contrato),
            ['servidor_id' => $servidorNovo->id]
        );

        $response->assertRedirect(route('tenant.contratos.show', $contrato));

        // Fiscal anterior desativado
        $this->assertDatabaseHas('fiscais', [
            'contrato_id' => $contrato->id,
            'servidor_id' => $servidorAnterior->id,
            'is_atual' => false,
        ], 'tenant');

        // Novo fiscal ativo
        $this->assertDatabaseHas('fiscais', [
            'contrato_id' => $contrato->id,
            'servidor_id' => $servidorNovo->id,
            'is_atual' => true,
        ], 'tenant');
    }

    public function test_troca_fiscal_registra_auditoria(): void
    {
        $secretaria = Secretaria::factory()->create();
        $contrato = Contrato::factory()->create(['secretaria_id' => $secretaria->id]);
        $servidorAnterior = Servidor::factory()->create([
            'secretaria_id' => $secretaria->id,
            'nome' => 'Fiscal Anterior',
        ]);
        $servidorNovo = Servidor::factory()->create([
            'secretaria_id' => $secretaria->id,
            'nome' => 'Fiscal Novo',
        ]);

        Fiscal::create([
            'contrato_id' => $contrato->id,
            'servidor_id' => $servidorAnterior->id,
            'nome' => $servidorAnterior->nome,
            'matricula' => $servidorAnterior->matricula,
            'cargo' => $servidorAnterior->cargo,
            'email' => $servidorAnterior->email,
            'data_inicio' => now()->subMonth()->toDateString(),
            'is_atual' => true,
        ]);

        $this->actAsAdmin()->post(
            route('tenant.contratos.fiscais.store', $contrato),
            ['servidor_id' => $servidorNovo->id]
        );

        // Verifica registro de auditoria
        $this->assertDatabaseHas('historico_alteracoes', [
            'auditable_type' => Contrato::class,
            'auditable_id' => $contrato->id,
            'campo_alterado' => 'fiscal_atual',
        ], 'tenant');
    }

    public function test_exige_permissao_fiscal_criar(): void
    {
        $contrato = Contrato::factory()->create();
        $servidor = Servidor::factory()->create();
        $role = Role::factory()->create(['nome' => 'role_sem_fiscal']);
        $user = User::factory()->create(['role_id' => $role->id, 'is_ativo' => true]);

        $response = $this->actingAs($user)
            ->withSession(['mfa_verified' => true])
            ->post(route('tenant.contratos.fiscais.store', $contrato), [
                'servidor_id' => $servidor->id,
            ]);

        $this->assertTrue(
            in_array($response->getStatusCode(), [403, 404]),
            'Esperado 403 ou 404, recebido: ' . $response->getStatusCode()
        );
    }

    public function test_valida_servidor_id_obrigatorio(): void
    {
        $contrato = Contrato::factory()->create();

        $response = $this->actAsAdmin()->post(
            route('tenant.contratos.fiscais.store', $contrato),
            []
        );

        $response->assertSessionHasErrors(['servidor_id']);
    }
}
