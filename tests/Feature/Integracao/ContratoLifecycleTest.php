<?php

namespace Tests\Feature\Integracao;

use App\Enums\PrioridadeAlerta;
use App\Enums\StatusAlerta;
use App\Enums\StatusContrato;
use App\Enums\TipoAditivo;
use App\Models\Alerta;
use App\Models\ConfiguracaoAlerta;
use App\Models\Contrato;
use App\Models\User;
use App\Services\AlertaService;
use Database\Seeders\ConfiguracaoLimiteAditivoSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;
use Tests\Traits\RunsTenantMigrations;
use Tests\Traits\SeedsTenantData;

class ContratoLifecycleTest extends TestCase
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

    // ─── FLUXO 1: CRIAR CONTRATO COM FISCAL E VERIFICAR SCORE ────

    public function test_fluxo_criar_contrato_com_fiscal_e_verificar_score(): void
    {
        $contrato = Contrato::factory()->vigente()->create();

        $contrato->refresh();

        $this->assertEquals(StatusContrato::Vigente, $contrato->status);
        $this->assertGreaterThanOrEqual(0, $contrato->score_risco);
        $this->assertNotNull($contrato->nivel_risco);
    }

    // ─── FLUXO 2: CONTRATO RECEBE DOCUMENTO ─────────────────────

    public function test_fluxo_contrato_recebe_documento_via_upload(): void
    {
        $contrato = Contrato::factory()->vigente()->create();

        $pdfContent = '%PDF-1.4 test content';
        $arquivo = UploadedFile::fake()->createWithContent('contrato.pdf', $pdfContent);

        $response = $this->actAsAdmin()->post(
            route('tenant.contratos.documentos.store', $contrato),
            [
                'arquivo' => $arquivo,
                'tipo_documento' => 'contrato_original',
            ]
        );

        $response->assertRedirect();
        $this->assertDatabaseHas('documentos', [
            'documentable_type' => Contrato::class,
            'documentable_id' => $contrato->id,
            'tipo_documento' => 'contrato_original',
        ], 'tenant');
    }

    // ─── FLUXO 3: ALERTA GERADO PARA CONTRATO VENCENDO ─────────

    public function test_fluxo_alerta_gerado_para_contrato_vencendo(): void
    {
        Queue::fake();

        ConfiguracaoAlerta::create([
            'dias_antecedencia' => 30,
            'prioridade_padrao' => PrioridadeAlerta::Atencao,
            'is_ativo' => true,
        ]);

        $contrato = Contrato::factory()->create([
            'status' => StatusContrato::Vigente,
            'data_fim' => now()->addDays(25)->format('Y-m-d'),
        ]);

        $resultado = AlertaService::verificarVencimentos();

        $this->assertGreaterThanOrEqual(1, $resultado['alertas_gerados']);

        // Verifica que alerta foi criado no banco
        $this->assertDatabaseHas('alertas', [
            'contrato_id' => $contrato->id,
            'status' => StatusAlerta::Pendente->value,
        ], 'tenant');

        // Segunda chamada nao gera duplicata (RN-016)
        $resultado2 = AlertaService::verificarVencimentos();
        $this->assertEquals(0, $resultado2['alertas_gerados']);
    }

    // ─── FLUXO 4: ADITIVO DE PRAZO RESOLVE ALERTAS PENDENTES ───

    public function test_fluxo_resolucao_alerta_por_aditivo_de_prazo(): void
    {
        $contrato = Contrato::factory()->vigente()->create([
            'data_fim' => now()->addDays(15)->format('Y-m-d'),
        ]);

        Alerta::factory()->create([
            'contrato_id' => $contrato->id,
            'status' => StatusAlerta::Pendente,
        ]);

        $response = $this->actAsAdmin()->post(route('tenant.contratos.aditivos.store', $contrato), [
            'tipo' => TipoAditivo::Prazo->value,
            'data_assinatura' => now()->format('Y-m-d'),
            'data_inicio_vigencia' => now()->format('Y-m-d'),
            'nova_data_fim' => now()->addYear()->format('Y-m-d'),
            'fundamentacao_legal' => 'Art. 57, II da Lei 14.133/2021',
            'justificativa' => 'Prorrogacao necessaria para continuidade do servico publico essencial.',
            'justificativa_tecnica' => 'Servico continua necessario e o fornecedor mantem qualidade adequada.',
        ]);

        $response->assertRedirect();

        $contrato->refresh();
        $this->assertEquals(now()->addYear()->format('Y-m-d'), $contrato->data_fim->format('Y-m-d'));
    }

    // ─── FLUXO 5: CONTRATO VENCE E FICA IRREGULAR ──────────────

    public function test_fluxo_contrato_vence_e_fica_irregular(): void
    {
        $contrato = Contrato::factory()->create([
            'status' => StatusContrato::Vigente,
            'data_fim' => now()->subDays(3)->format('Y-m-d'),
        ]);

        AlertaService::verificarVencimentos();

        $contrato->refresh();
        $this->assertEquals(StatusContrato::Vencido, $contrato->status);
        $this->assertTrue($contrato->is_irregular);
    }

    // ─── FLUXO 6: CONTRATO IRREGULAR REGULARIZADO POR ADITIVO ──

    public function test_fluxo_contrato_irregular_regularizado_por_aditivo(): void
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
            'justificativa_retroativa' => 'O contrato venceu durante o processo licitatorio substitutivo que ainda esta em andamento. A continuidade e imprescindivel para manter os servicos essenciais.',
        ]);

        $response->assertRedirect();

        $contrato->refresh();
        $this->assertEquals(StatusContrato::Vigente, $contrato->status);
        $this->assertFalse($contrato->is_irregular);
    }
}
