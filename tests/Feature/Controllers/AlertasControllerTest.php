<?php

namespace Tests\Feature\Controllers;

use App\Enums\StatusAlerta;
use App\Models\Alerta;
use App\Models\ConfiguracaoAlerta;
use App\Models\Contrato;
use App\Models\User;
use Tests\TestCase;
use Tests\Traits\RunsTenantMigrations;
use Tests\Traits\SeedsTenantData;

class AlertasControllerTest extends TestCase
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

    // ─── INDEX ─────────────────────────────────────────────

    public function test_index_exibe_listagem_de_alertas(): void
    {
        Alerta::factory()->count(3)->create();

        $response = $this->actAsAdmin()->get(route('tenant.alertas.index'));

        $response->assertStatus(200);
        $response->assertViewIs('tenant.alertas.index');
    }

    public function test_index_requer_autenticacao(): void
    {
        $response = $this->get(route('tenant.alertas.index'));
        $response->assertRedirect();
    }

    public function test_index_usuario_sem_permissao_retorna_403(): void
    {
        $role = \App\Models\Role::factory()->create(['nome' => 'role_sem_permissao']);
        $user = \App\Models\User::factory()->create(['role_id' => $role->id]);
        $response = $this->actingAs($user)->get(route('tenant.alertas.index'));
        $response->assertStatus(403);
    }

    public function test_index_funciona_sem_alertas(): void
    {
        $response = $this->actAsAdmin()->get(route('tenant.alertas.index'));
        $response->assertStatus(200);
    }

    // ─── SHOW ──────────────────────────────────────────────

    public function test_show_exibe_detalhes_do_alerta(): void
    {
        $alerta = Alerta::factory()->create();

        $response = $this->actAsAdmin()->get(route('tenant.alertas.show', $alerta));

        $response->assertStatus(200);
        $response->assertViewIs('tenant.alertas.show');
        $response->assertViewHas('alerta');
    }

    // ─── RESOLVER ──────────────────────────────────────────

    public function test_resolver_alerta_com_sucesso(): void
    {
        $alerta = Alerta::factory()->create([
            'status' => StatusAlerta::Pendente,
        ]);

        $response = $this->actAsAdmin()->post(route('tenant.alertas.resolver', $alerta), [
            'observacao' => 'Contrato renovado com sucesso',
        ]);

        $response->assertRedirect();

        $alerta->refresh();
        $this->assertEquals(StatusAlerta::Resolvido, $alerta->status);
    }

    public function test_resolver_exige_permissao_alerta_resolver(): void
    {
        $alerta = Alerta::factory()->create();
        $user = $this->createUserWithRole('secretario');

        $response = $this->actingAs($user)->post(route('tenant.alertas.resolver', $alerta));

        $response->assertStatus(403);
    }

    // ─── CONFIGURAÇÕES ─────────────────────────────────────

    public function test_configuracoes_exibe_tela(): void
    {
        $response = $this->actAsAdmin()->get(route('tenant.alertas.configuracoes'));

        $response->assertStatus(200);
        $response->assertViewIs('tenant.alertas.configuracoes');
    }

    public function test_configuracoes_exige_permissao(): void
    {
        $user = $this->createUserWithRole('secretario');

        $response = $this->actingAs($user)->get(route('tenant.alertas.configuracoes'));

        $response->assertStatus(403);
    }

    public function test_salvar_configuracoes_com_sucesso(): void
    {
        $config = ConfiguracaoAlerta::factory()->create();

        // Formato esperado pelo controller: array de objetos com id + is_ativo
        $response = $this->actAsAdmin()->post(route('tenant.alertas.salvar-configuracoes'), [
            'configuracoes' => [
                [
                    'id' => $config->id,
                    'is_ativo' => '1',
                ],
            ],
        ]);

        $response->assertRedirect(route('tenant.alertas.configuracoes'));
        $response->assertSessionHas('success');
    }
}
