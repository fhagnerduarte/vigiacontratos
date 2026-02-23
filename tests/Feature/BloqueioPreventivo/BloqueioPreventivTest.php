<?php

namespace Tests\Feature\BloqueioPreventivo;

use App\Enums\StatusContrato;
use App\Enums\TipoAditivo;
use App\Models\ConfiguracaoLimiteAditivo;
use App\Models\Contrato;
use App\Models\User;
use App\Services\AlertaService;
use Database\Seeders\ConfiguracaoLimiteAditivoSeeder;
use Tests\TestCase;
use Tests\Traits\RunsTenantMigrations;
use Tests\Traits\SeedsTenantData;

class BloqueioPreventivTest extends TestCase
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

    // ─── RN-046: MARCAR CONTRATOS COMO IRREGULAR ──────────────

    public function test_marcar_contratos_vencidos_seta_is_irregular_true(): void
    {
        $contrato = Contrato::factory()->create([
            'status' => StatusContrato::Vigente,
            'data_fim' => now()->subDays(5)->format('Y-m-d'),
        ]);

        AlertaService::verificarVencimentos();

        $contrato->refresh();
        $this->assertEquals(StatusContrato::Vencido, $contrato->status);
        $this->assertTrue($contrato->is_irregular);
    }

    public function test_contrato_ja_vencido_nao_e_remarcado(): void
    {
        $contrato = Contrato::factory()->create([
            'status' => StatusContrato::Vencido,
            'data_fim' => now()->subDays(30)->format('Y-m-d'),
            'is_irregular' => true,
        ]);

        $resultado = AlertaService::verificarVencimentos();

        // Contrato ja era vencido, nao entra no count de novos vencidos
        $this->assertEquals(0, $resultado['contratos_vencidos']);
    }

    public function test_contrato_vigente_com_data_futura_nao_e_afetado(): void
    {
        $contrato = Contrato::factory()->vigente()->create();

        AlertaService::verificarVencimentos();

        $contrato->refresh();
        $this->assertEquals(StatusContrato::Vigente, $contrato->status);
        $this->assertFalse($contrato->is_irregular);
    }

    // ─── RN-006: BLOQUEIO DE EDICAO ──────────────────────────

    public function test_edit_contrato_vencido_redireciona_com_erro(): void
    {
        $contrato = Contrato::factory()->vencido()->create();

        $response = $this->actAsAdmin()->get(route('tenant.contratos.edit', $contrato));

        $response->assertRedirect(route('tenant.contratos.show', $contrato));
        $response->assertSessionHas('error');
    }

    public function test_update_contrato_vencido_retorna_403(): void
    {
        $contrato = Contrato::factory()->vencido()->create();

        $response = $this->actAsAdmin()->put(route('tenant.contratos.update', $contrato), [
            'objeto' => 'Tentativa de alteracao',
        ]);

        // UpdateContratoRequest::authorize() retorna false para vencido → 403
        $response->assertStatus(403);
    }

    // ─── RN-052: ADITIVO RETROATIVO COM JUSTIFICATIVA ────────

    public function test_create_aditivo_contrato_vencido_exibe_formulario(): void
    {
        $contrato = Contrato::factory()->vencido()->create();

        $response = $this->actAsAdmin()->get(route('tenant.contratos.aditivos.create', $contrato));

        $response->assertStatus(200);
        $response->assertViewIs('tenant.aditivos.create');
        $response->assertViewHas('exigeJustificativaRetroativa', true);
    }

    public function test_create_aditivo_contrato_vencido_exibe_campo_justificativa(): void
    {
        $contrato = Contrato::factory()->vencido()->create();

        $response = $this->actAsAdmin()->get(route('tenant.contratos.aditivos.create', $contrato));

        $response->assertStatus(200);
        $response->assertSee('Justificativa Retroativa');
        $response->assertSee('RN-052');
    }

    public function test_store_aditivo_vencido_sem_justificativa_retroativa_falha(): void
    {
        $contrato = Contrato::factory()->vencido()->create();

        $response = $this->actAsAdmin()->post(route('tenant.contratos.aditivos.store', $contrato), [
            'tipo' => TipoAditivo::Prazo->value,
            'data_assinatura' => now()->format('Y-m-d'),
            'data_inicio_vigencia' => now()->format('Y-m-d'),
            'nova_data_fim' => now()->addMonths(6)->format('Y-m-d'),
            'fundamentacao_legal' => 'Art. 57, II da Lei 14.133/2021',
            'justificativa' => 'Necessidade de continuidade do servico publico.',
            'justificativa_tecnica' => 'Servico essencial sem substituto imediato disponivel.',
        ]);

        $response->assertSessionHasErrors('justificativa_retroativa');
    }

    public function test_store_aditivo_vencido_justificativa_curta_falha(): void
    {
        $contrato = Contrato::factory()->vencido()->create();

        $response = $this->actAsAdmin()->post(route('tenant.contratos.aditivos.store', $contrato), [
            'tipo' => TipoAditivo::Prazo->value,
            'data_assinatura' => now()->format('Y-m-d'),
            'data_inicio_vigencia' => now()->format('Y-m-d'),
            'nova_data_fim' => now()->addMonths(6)->format('Y-m-d'),
            'fundamentacao_legal' => 'Art. 57, II da Lei 14.133/2021',
            'justificativa' => 'Necessidade de continuidade do servico publico.',
            'justificativa_tecnica' => 'Servico essencial sem substituto.',
            'justificativa_retroativa' => 'Curta demais',
        ]);

        $response->assertSessionHasErrors('justificativa_retroativa');
    }

    public function test_store_aditivo_vencido_com_justificativa_retroativa_sucesso(): void
    {
        $contrato = Contrato::factory()->vencido()->create();

        $response = $this->actAsAdmin()->post(route('tenant.contratos.aditivos.store', $contrato), [
            'tipo' => TipoAditivo::Prazo->value,
            'data_assinatura' => now()->format('Y-m-d'),
            'data_inicio_vigencia' => now()->format('Y-m-d'),
            'nova_data_fim' => now()->addMonths(6)->format('Y-m-d'),
            'fundamentacao_legal' => 'Art. 57, II da Lei 14.133/2021',
            'justificativa' => 'Necessidade de continuidade do servico publico essencial.',
            'justificativa_tecnica' => 'Servico essencial sem substituto imediato disponivel no mercado.',
            'justificativa_retroativa' => 'O contrato venceu durante o processo licitatorio substitutivo que ainda esta em andamento. A continuidade e necessaria para nao interromper servicos essenciais.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
    }

    public function test_store_aditivo_vigente_nao_exige_justificativa_retroativa(): void
    {
        $contrato = Contrato::factory()->vigente()->create();

        $response = $this->actAsAdmin()->post(route('tenant.contratos.aditivos.store', $contrato), [
            'tipo' => TipoAditivo::Prazo->value,
            'data_assinatura' => now()->format('Y-m-d'),
            'data_inicio_vigencia' => now()->format('Y-m-d'),
            'nova_data_fim' => now()->addMonths(12)->format('Y-m-d'),
            'fundamentacao_legal' => 'Art. 57, II da Lei 14.133/2021',
            'justificativa' => 'Prorrogacao de prazo por interesse da administracao publica.',
            'justificativa_tecnica' => 'Servico continua necessario e o fornecedor mantem qualidade.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
    }

    public function test_create_aditivo_contrato_cancelado_bloqueado(): void
    {
        $contrato = Contrato::factory()->create([
            'status' => StatusContrato::Cancelado,
            'data_fim' => now()->addMonths(3)->format('Y-m-d'),
        ]);

        $response = $this->actAsAdmin()->get(route('tenant.contratos.aditivos.create', $contrato));

        $response->assertRedirect(route('tenant.contratos.show', $contrato));
        $response->assertSessionHas('error');
    }

    // ─── RN-046: REGULARIZACAO ───────────────────────────────

    public function test_aditivo_prazo_em_vencido_regulariza_contrato(): void
    {
        $contrato = Contrato::factory()->vencido()->create();
        $this->assertTrue($contrato->is_irregular);

        $response = $this->actAsAdmin()->post(route('tenant.contratos.aditivos.store', $contrato), [
            'tipo' => TipoAditivo::Prazo->value,
            'data_assinatura' => now()->format('Y-m-d'),
            'data_inicio_vigencia' => now()->format('Y-m-d'),
            'nova_data_fim' => now()->addMonths(6)->format('Y-m-d'),
            'fundamentacao_legal' => 'Art. 57, II da Lei 14.133/2021',
            'justificativa' => 'Prorrogacao necessaria para continuidade do servico publico essencial.',
            'justificativa_tecnica' => 'Nao ha fornecedor substituto e o processo licitatorio esta em andamento.',
            'justificativa_retroativa' => 'O contrato venceu durante o processo licitatorio substitutivo que ainda esta em andamento. A continuidade e imprescindivel.',
        ]);

        $response->assertRedirect();

        $contrato->refresh();
        $this->assertEquals(StatusContrato::Vigente, $contrato->status);
        $this->assertFalse($contrato->is_irregular);
    }

    public function test_aditivo_valor_em_vencido_nao_regulariza(): void
    {
        $contrato = Contrato::factory()->vencido()->create();

        $response = $this->actAsAdmin()->post(route('tenant.contratos.aditivos.store', $contrato), [
            'tipo' => TipoAditivo::Valor->value,
            'data_assinatura' => now()->format('Y-m-d'),
            'data_inicio_vigencia' => now()->format('Y-m-d'),
            'valor_acrescimo' => 5000.00,
            'fundamentacao_legal' => 'Art. 65, I, b da Lei 14.133/2021',
            'justificativa' => 'Acrescimo necessario para cobrir demanda adicional identificada.',
            'justificativa_tecnica' => 'Demanda excedente comprovada em relatorio tecnico anexo ao processo.',
            'justificativa_retroativa' => 'O contrato venceu mas ha necessidade de acrescimo de valor para regularizar pagamentos pendentes ao fornecedor.',
        ]);

        $response->assertRedirect();

        $contrato->refresh();
        // Aditivo de valor nao tem nova_data_fim, nao regulariza
        $this->assertEquals(StatusContrato::Vencido, $contrato->status);
        $this->assertTrue($contrato->is_irregular);
    }

    public function test_regularizar_contrato_limpa_flag_irregular(): void
    {
        $contrato = Contrato::factory()->irregular()->create();
        $this->assertTrue($contrato->is_irregular);

        AlertaService::regularizarContrato($contrato);

        $contrato->refresh();
        $this->assertFalse($contrato->is_irregular);
    }

    // ─── UI: INDICADORES VISUAIS ─────────────────────────────

    public function test_index_mostra_badge_irregular_para_contrato_irregular(): void
    {
        Contrato::factory()->irregular()->create();

        $response = $this->actAsAdmin()->get(route('tenant.contratos.index'));

        $response->assertStatus(200);
        $response->assertSee('IRREGULAR');
    }

    public function test_show_mostra_banner_irregular_para_contrato_vencido(): void
    {
        $contrato = Contrato::factory()->irregular()->create();

        $response = $this->actAsAdmin()->get(route('tenant.contratos.show', $contrato));

        $response->assertStatus(200);
        $response->assertSee('Contrato IRREGULAR (RN-046)');
        $response->assertSee('Adicionar Aditivo Retroativo');
    }

    // ─── DASHBOARD: CONTADOR IRREGULAR ───────────────────────

    public function test_dashboard_visao_controlador_conta_irregulares(): void
    {
        // Captura total antes de criar novos
        $antes = Contrato::where('is_irregular', true)->count();

        Contrato::factory()->irregular()->count(3)->create();
        Contrato::factory()->vigente()->create();

        $resultado = \App\Services\DashboardService::visaoControlador();

        $irregulares = collect($resultado['irregularidades'])
            ->firstWhere('label', 'Contratos irregulares');

        $this->assertNotNull($irregulares);
        $this->assertEquals($antes + 3, $irregulares['total']);
    }
}
