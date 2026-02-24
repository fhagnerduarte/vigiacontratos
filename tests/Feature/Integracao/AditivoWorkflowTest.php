<?php

namespace Tests\Feature\Integracao;

use App\Enums\EtapaWorkflow;
use App\Enums\StatusAprovacao;
use App\Enums\TipoAditivo;
use App\Models\Contrato;
use App\Models\User;
use App\Models\WorkflowAprovacao;
use App\Services\AditivoService;
use App\Services\WorkflowService;
use Database\Seeders\ConfiguracaoLimiteAditivoSeeder;
use Tests\TestCase;
use Tests\Traits\RunsTenantMigrations;
use Tests\Traits\SeedsTenantData;

class AditivoWorkflowTest extends TestCase
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

    private function criarAditivoComWorkflow(): array
    {
        $contrato = Contrato::factory()->vigente()->create();

        $aditivo = AditivoService::criar([
            'tipo' => TipoAditivo::Prazo->value,
            'data_assinatura' => now()->format('Y-m-d'),
            'data_inicio_vigencia' => now()->format('Y-m-d'),
            'nova_data_fim' => now()->addYear()->format('Y-m-d'),
            'fundamentacao_legal' => 'Art. 57, II da Lei 14.133/2021',
            'justificativa' => 'Prorrogacao necessaria para continuidade do servico.',
            'justificativa_tecnica' => 'Servico essencial sem substituto disponivel.',
        ], $contrato, $this->admin, '127.0.0.1');

        return [$contrato, $aditivo];
    }

    // ─── FLUXO 1: CRIAR ADITIVO GERA WORKFLOW 5 ETAPAS ─────────

    public function test_fluxo_criar_aditivo_gera_workflow_5_etapas(): void
    {
        [$contrato, $aditivo] = $this->criarAditivoComWorkflow();

        $etapas = WorkflowAprovacao::where('aprovavel_type', $aditivo->getMorphClass())
            ->where('aprovavel_id', $aditivo->id)
            ->orderBy('etapa_ordem')
            ->get();

        $this->assertCount(5, $etapas);

        // Etapa 1 (Solicitacao) auto-aprovada
        $this->assertEquals(StatusAprovacao::Aprovado, $etapas[0]->status);
        $this->assertEquals(EtapaWorkflow::Solicitacao->value, $etapas[0]->etapa->value);

        // Etapas 2-5 pendentes
        for ($i = 1; $i < 5; $i++) {
            $this->assertEquals(StatusAprovacao::Pendente, $etapas[$i]->status);
        }

        // Etapa atual deve ser a 2 (AprovacaoSecretario)
        $etapaAtual = WorkflowService::obterEtapaAtual($aditivo);
        $this->assertEquals(EtapaWorkflow::AprovacaoSecretario->value, $etapaAtual->etapa->value);
    }

    // ─── FLUXO 2: APROVACAO SEQUENCIAL 4 ETAPAS ────────────────

    public function test_fluxo_workflow_aprovacao_sequencial_4_etapas(): void
    {
        [$contrato, $aditivo] = $this->criarAditivoComWorkflow();

        // Aprovar etapa 2 (AprovacaoSecretario)
        $etapa2 = WorkflowService::obterEtapaAtual($aditivo);
        WorkflowService::aprovar($etapa2, $this->admin, 'Aprovado pelo secretario.', '127.0.0.1');

        // Apos aprovar etapa 2, etapa atual deve ser 3 (ParecerJuridico)
        $etapa3 = WorkflowService::obterEtapaAtual($aditivo);
        $this->assertEquals(EtapaWorkflow::ParecerJuridico->value, $etapa3->etapa->value);

        // Aprovar etapa 3
        WorkflowService::aprovar($etapa3, $this->admin, 'Parecer juridico favoravel.', '127.0.0.1');

        // Etapa atual deve ser 4 (ValidacaoControladoria)
        $etapa4 = WorkflowService::obterEtapaAtual($aditivo);
        $this->assertEquals(EtapaWorkflow::ValidacaoControladoria->value, $etapa4->etapa->value);
    }

    // ─── FLUXO 3: WORKFLOW REPROVADO BLOQUEIA AVANCO ────────────

    public function test_fluxo_workflow_reprovado_bloqueia_avanco(): void
    {
        [$contrato, $aditivo] = $this->criarAditivoComWorkflow();

        $etapa2 = WorkflowService::obterEtapaAtual($aditivo);
        WorkflowService::reprovar($etapa2, $this->admin, 'Documentacao insuficiente.', '127.0.0.1');

        $etapa2->refresh();
        $this->assertEquals(StatusAprovacao::Reprovado, $etapa2->status);

        // Etapa atual continua sendo a mesma posicao (nao avancou)
        // A proxima etapa pendente e a 3, mas a 2 nao foi aprovada
        // Tentar aprovar etapa 3 deve lancar excecao
        $etapa3 = WorkflowAprovacao::where('aprovavel_type', $aditivo->getMorphClass())
            ->where('aprovavel_id', $aditivo->id)
            ->where('etapa_ordem', 3)
            ->first();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('A etapa anterior precisa ser aprovada primeiro (RN-337).');
        WorkflowService::aprovar($etapa3, $this->admin, 'Tentativa invalida.', '127.0.0.1');
    }

    // ─── FLUXO 4: BLOQUEIO SEQUENCIAL ──────────────────────────

    public function test_fluxo_bloqueio_sequencial_etapa_anterior_pendente(): void
    {
        [$contrato, $aditivo] = $this->criarAditivoComWorkflow();

        // Tentar aprovar etapa 3 sem aprovar etapa 2
        $etapa3 = WorkflowAprovacao::where('aprovavel_type', $aditivo->getMorphClass())
            ->where('aprovavel_id', $aditivo->id)
            ->where('etapa_ordem', 3)
            ->first();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('A etapa anterior precisa ser aprovada primeiro (RN-337).');
        WorkflowService::aprovar($etapa3, $this->admin, 'Tentativa invalida.', '127.0.0.1');
    }

    // ─── FLUXO 5: APROVACAO COMPLETA VIA HTTP ──────────────────

    public function test_fluxo_aprovacao_completa_5_etapas_via_http(): void
    {
        $contrato = Contrato::factory()->vigente()->create();

        // Criar aditivo via HTTP
        $response = $this->actAsAdmin()->post(route('tenant.contratos.aditivos.store', $contrato), [
            'tipo' => TipoAditivo::Prazo->value,
            'data_assinatura' => now()->format('Y-m-d'),
            'data_inicio_vigencia' => now()->format('Y-m-d'),
            'nova_data_fim' => now()->addYear()->format('Y-m-d'),
            'fundamentacao_legal' => 'Art. 57, II da Lei 14.133/2021',
            'justificativa' => 'Prorrogacao necessaria para continuidade do servico publico essencial.',
            'justificativa_tecnica' => 'Servico essencial sem substituto imediato disponivel.',
        ]);
        $response->assertRedirect();

        $aditivo = $contrato->aditivos()->latest()->first();
        $this->assertNotNull($aditivo);

        // Aprovar etapas 2 a 5 via HTTP (rota usa Aditivo como parametro)
        $pendentes = WorkflowAprovacao::where('aprovavel_type', $aditivo->getMorphClass())
            ->where('aprovavel_id', $aditivo->id)
            ->where('status', StatusAprovacao::Pendente->value)
            ->count();

        for ($i = 0; $i < $pendentes; $i++) {
            $response = $this->actAsAdmin()->post(route('tenant.aditivos.aprovar', $aditivo), [
                'parecer' => 'Aprovado na etapa ' . ($i + 2),
            ]);
            $response->assertRedirect();
        }

        $this->assertTrue(WorkflowService::isAprovado($aditivo));
    }

    // ─── FLUXO 6: VALOR CONTRATO ATUALIZADO APOS ADITIVO VALOR ─

    public function test_fluxo_valor_contrato_atualizado_apos_aditivo_valor(): void
    {
        $contrato = Contrato::factory()->vigente()->create([
            'valor_global' => 100000.00,
        ]);

        $response = $this->actAsAdmin()->post(route('tenant.contratos.aditivos.store', $contrato), [
            'tipo' => TipoAditivo::Valor->value,
            'data_assinatura' => now()->format('Y-m-d'),
            'data_inicio_vigencia' => now()->format('Y-m-d'),
            'valor_acrescimo' => 20000.00,
            'fundamentacao_legal' => 'Art. 65, I, b da Lei 14.133/2021',
            'justificativa' => 'Acrescimo necessario para cobrir demanda adicional identificada.',
            'justificativa_tecnica' => 'Demanda excedente comprovada em relatorio tecnico anexo ao processo.',
        ]);

        $response->assertRedirect();

        $contrato->refresh();
        $this->assertEquals(120000.00, (float) $contrato->valor_global);
    }

    // ─── FLUXO 7: CANCELAMENTO ADITIVO RESTAURA CONTRATO ───────

    public function test_fluxo_cancelamento_aditivo_restaura_contrato(): void
    {
        $contrato = Contrato::factory()->vigente()->create([
            'valor_global' => 100000.00,
        ]);

        $aditivo = AditivoService::criar([
            'tipo' => TipoAditivo::Valor->value,
            'data_assinatura' => now()->format('Y-m-d'),
            'data_inicio_vigencia' => now()->format('Y-m-d'),
            'valor_acrescimo' => 20000.00,
            'fundamentacao_legal' => 'Art. 65, I, b da Lei 14.133/2021',
            'justificativa' => 'Acrescimo necessario para demanda adicional.',
            'justificativa_tecnica' => 'Demanda excedente comprovada.',
        ], $contrato, $this->admin, '127.0.0.1');

        $contrato->refresh();
        $this->assertEquals(120000.00, (float) $contrato->valor_global);

        AditivoService::cancelar($aditivo, $this->admin, '127.0.0.1');

        $contrato->refresh();
        $this->assertEquals(100000.00, (float) $contrato->valor_global);
    }
}
