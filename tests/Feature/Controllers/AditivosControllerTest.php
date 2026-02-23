<?php

namespace Tests\Feature\Controllers;

use App\Enums\StatusAditivo;
use App\Enums\TipoAditivo;
use App\Models\Aditivo;
use App\Models\ConfiguracaoLimiteAditivo;
use App\Models\Contrato;
use App\Models\User;
use Database\Seeders\ConfiguracaoLimiteAditivoSeeder;
use Tests\TestCase;
use Tests\Traits\RunsTenantMigrations;
use Tests\Traits\SeedsTenantData;

class AditivosControllerTest extends TestCase
{
    use RunsTenantMigrations;
    use SeedsTenantData;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpTenant();
        $this->seedBaseData();
        $this->seed(ConfiguracaoLimiteAditivoSeeder::class);
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

    public function test_index_exibe_dashboard_de_aditivos(): void
    {
        $response = $this->actAsAdmin()->get(route('tenant.aditivos.index'));

        $response->assertStatus(200);
        $response->assertViewIs('tenant.aditivos.index');
    }

    public function test_index_requer_autenticacao(): void
    {
        $response = $this->get(route('tenant.aditivos.index'));
        $response->assertRedirect();
    }

    public function test_index_usuario_sem_permissao_retorna_403(): void
    {
        $role = \App\Models\Role::factory()->create(['nome' => 'role_sem_permissao']);
        $user = \App\Models\User::factory()->create(['role_id' => $role->id]);
        $response = $this->actingAs($user)->get(route('tenant.aditivos.index'));
        $response->assertStatus(403);
    }

    // ─── CREATE ────────────────────────────────────────────

    public function test_create_exibe_formulario_para_contrato(): void
    {
        $contrato = Contrato::factory()->vigente()->create();

        $response = $this->actAsAdmin()->get(route('tenant.contratos.aditivos.create', $contrato));

        $response->assertStatus(200);
        $response->assertViewIs('tenant.aditivos.create');
        $response->assertViewHas('contrato');
    }

    // ─── STORE ─────────────────────────────────────────────

    public function test_store_cria_aditivo_de_prazo_com_sucesso(): void
    {
        $contrato = Contrato::factory()->vigente()->create([
            'data_fim' => now()->addMonths(2)->format('Y-m-d'),
        ]);

        $dados = [
            'tipo' => TipoAditivo::Prazo->value,
            'data_assinatura' => now()->format('Y-m-d'),
            'data_inicio_vigencia' => now()->format('Y-m-d'),
            'nova_data_fim' => now()->addYear()->format('Y-m-d'),
            'justificativa' => 'Necessidade de continuidade dos serviços essenciais ao município.',
            'justificativa_tecnica' => 'Os serviços são contínuos e não podem ser interrompidos sem prejuízo à administração.',
            'fundamentacao_legal' => 'Art. 107 da Lei 14.133/2021',
        ];

        $response = $this->actAsAdmin()->post(route('tenant.contratos.aditivos.store', $contrato), $dados);

        $response->assertRedirect();
        $response->assertSessionDoesntHaveErrors();

        $this->assertDatabaseHas('aditivos', [
            'contrato_id' => $contrato->id,
            'tipo' => TipoAditivo::Prazo->value,
        ], 'tenant');
    }

    public function test_store_valida_tipo_obrigatorio(): void
    {
        $contrato = Contrato::factory()->vigente()->create();

        $response = $this->actAsAdmin()->post(route('tenant.contratos.aditivos.store', $contrato), [
            'tipo' => '',
            'data_assinatura' => now()->format('Y-m-d'),
            'justificativa' => 'Justificativa de teste',
        ]);

        $response->assertSessionHasErrors('tipo');
    }

    public function test_store_valida_nova_data_fim_apos_data_atual(): void
    {
        $contrato = Contrato::factory()->vigente()->create([
            'data_fim' => now()->addMonths(6)->format('Y-m-d'),
        ]);

        $response = $this->actAsAdmin()->post(route('tenant.contratos.aditivos.store', $contrato), [
            'tipo' => TipoAditivo::Prazo->value,
            'data_assinatura' => now()->format('Y-m-d'),
            'data_inicio_vigencia' => now()->format('Y-m-d'),
            'nova_data_fim' => now()->subMonth()->format('Y-m-d'),
            'justificativa' => 'Teste',
            'fundamentacao_legal' => 'Art. 107',
        ]);

        $response->assertSessionHasErrors('nova_data_fim');
    }

    // ─── SHOW ──────────────────────────────────────────────

    public function test_show_exibe_detalhes_do_aditivo(): void
    {
        $aditivo = Aditivo::factory()->create();

        $response = $this->actAsAdmin()->get(route('tenant.aditivos.show', $aditivo));

        $response->assertStatus(200);
        $response->assertViewIs('tenant.aditivos.show');
        $response->assertViewHas('aditivo');
    }

    // ─── APROVAR ───────────────────────────────────────────

    public function test_aprovar_exige_permissao(): void
    {
        $aditivo = Aditivo::factory()->create();
        $user = $this->createUserWithRole('fiscal_contrato');

        $response = $this->actingAs($user)->post(route('tenant.aditivos.aprovar', $aditivo));

        $response->assertStatus(403);
    }

    // ─── REPROVAR ──────────────────────────────────────────

    public function test_reprovar_exige_permissao(): void
    {
        $aditivo = Aditivo::factory()->create();
        $user = $this->createUserWithRole('fiscal_contrato');

        $response = $this->actingAs($user)->post(route('tenant.aditivos.reprovar', $aditivo), [
            'parecer' => 'Valores incompatíveis com o mercado',
        ]);

        $response->assertStatus(403);
    }

    // ─── CANCELAR ──────────────────────────────────────────

    public function test_cancelar_exige_permissao(): void
    {
        $aditivo = Aditivo::factory()->create();
        $user = $this->createUserWithRole('fiscal_contrato');

        $response = $this->actingAs($user)->post(route('tenant.aditivos.cancelar', $aditivo));

        $response->assertStatus(403);
    }

    public function test_cancelar_funciona_para_admin(): void
    {
        $contrato = Contrato::factory()->vigente()->create();
        $aditivo = Aditivo::factory()->create([
            'contrato_id' => $contrato->id,
            'status' => StatusAditivo::Vigente,
        ]);

        $response = $this->actAsAdmin()->post(route('tenant.aditivos.cancelar', $aditivo));

        $response->assertRedirect();

        $aditivo->refresh();
        $this->assertEquals(StatusAditivo::Cancelado, $aditivo->status);
    }
}
