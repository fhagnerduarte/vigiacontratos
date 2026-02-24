<?php

namespace Tests\Feature\Controllers;

use App\Models\Contrato;
use App\Models\Role;
use App\Models\User;
use Tests\TestCase;
use Tests\Traits\RunsTenantMigrations;
use Tests\Traits\SeedsTenantData;

class ExecucoesFinanceirasControllerTest extends TestCase
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

    public function test_registra_execucao_financeira_com_sucesso(): void
    {
        $contrato = Contrato::factory()->create(['valor_global' => 100000]);

        $response = $this->actAsAdmin()->post(
            route('tenant.contratos.execucoes.store', $contrato),
            [
                'descricao' => 'Pagamento parcela 1',
                'valor' => 10000.00,
                'data_execucao' => now()->format('Y-m-d'),
                'numero_nota_fiscal' => 'NF-001',
            ]
        );

        $response->assertRedirect(route('tenant.contratos.show', $contrato));
        $response->assertSessionHas('success');
    }

    public function test_valida_campos_obrigatorios(): void
    {
        $contrato = Contrato::factory()->create();

        $response = $this->actAsAdmin()->post(
            route('tenant.contratos.execucoes.store', $contrato),
            []
        );

        $response->assertSessionHasErrors(['descricao', 'valor', 'data_execucao']);
    }

    public function test_dispara_alerta_quando_valor_excede_contrato(): void
    {
        $contrato = Contrato::factory()->create(['valor_global' => 1000]);

        $response = $this->actAsAdmin()->post(
            route('tenant.contratos.execucoes.store', $contrato),
            [
                'descricao' => 'Pagamento excedente',
                'valor' => 1500.00,
                'data_execucao' => now()->format('Y-m-d'),
            ]
        );

        $response->assertRedirect(route('tenant.contratos.show', $contrato));
        $response->assertSessionHas('warning');
    }

    public function test_exige_permissao_financeiro_registrar_empenho(): void
    {
        $contrato = Contrato::factory()->create();
        $role = Role::factory()->create(['nome' => 'role_sem_permissao_fin']);
        $user = User::factory()->create(['role_id' => $role->id, 'is_ativo' => true]);

        $response = $this->actingAs($user)
            ->withSession(['mfa_verified' => true])
            ->post(route('tenant.contratos.execucoes.store', $contrato), [
                'descricao' => 'Teste',
                'valor' => 100,
                'data_execucao' => now()->format('Y-m-d'),
            ]);

        $this->assertTrue(
            in_array($response->getStatusCode(), [403, 404]),
            'Esperado 403 ou 404, recebido: ' . $response->getStatusCode()
        );
    }

    public function test_valor_deve_ser_maior_que_zero(): void
    {
        $contrato = Contrato::factory()->create();

        $response = $this->actAsAdmin()->post(
            route('tenant.contratos.execucoes.store', $contrato),
            [
                'descricao' => 'Valor invalido',
                'valor' => 0,
                'data_execucao' => now()->format('Y-m-d'),
            ]
        );

        $response->assertSessionHasErrors(['valor']);
    }
}
