<?php

namespace Tests\Unit\Services;

use App\Enums\EtapaWorkflow;
use App\Enums\StatusAprovacao;
use App\Models\Aditivo;
use App\Models\Contrato;
use App\Models\WorkflowAprovacao;
use App\Services\WorkflowService;
use Tests\TestCase;
use Tests\Traits\RunsTenantMigrations;
use Tests\Traits\SeedsTenantData;

class WorkflowServiceTest extends TestCase
{
    use RunsTenantMigrations;
    use SeedsTenantData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpTenant();
        $this->seedBaseData();
    }

    // --- criarFluxo ---

    public function test_criar_fluxo_5_etapas(): void
    {
        $user = $this->createAdminUser();
        $aditivo = Aditivo::factory()->create();

        $etapas = WorkflowService::criarFluxo($aditivo, $user, '127.0.0.1');

        $this->assertCount(5, $etapas);
    }

    public function test_criar_fluxo_etapa_1_auto_aprovada(): void
    {
        $user = $this->createAdminUser();
        $aditivo = Aditivo::factory()->create();

        $etapas = WorkflowService::criarFluxo($aditivo, $user, '127.0.0.1');

        $primeiraEtapa = $etapas->first();
        $this->assertEquals(StatusAprovacao::Aprovado->value, $primeiraEtapa->status->value);
        $this->assertEquals($user->id, $primeiraEtapa->user_id);
        $this->assertNotNull($primeiraEtapa->decided_at);
    }

    public function test_criar_fluxo_demais_etapas_pendentes(): void
    {
        $user = $this->createAdminUser();
        $aditivo = Aditivo::factory()->create();

        $etapas = WorkflowService::criarFluxo($aditivo, $user, '127.0.0.1');

        $pendentes = $etapas->filter(fn ($e) => $e->status === StatusAprovacao::Pendente);
        $this->assertCount(4, $pendentes);
    }

    public function test_criar_fluxo_ordem_sequencial(): void
    {
        $user = $this->createAdminUser();
        $aditivo = Aditivo::factory()->create();

        $etapas = WorkflowService::criarFluxo($aditivo, $user, '127.0.0.1');

        $ordens = $etapas->pluck('etapa_ordem')->toArray();
        $this->assertEquals([1, 2, 3, 4, 5], $ordens);
    }

    // --- aprovar ---

    public function test_aprovar_etapa_sequencial(): void
    {
        $user = $this->createAdminUser();
        $aditivo = Aditivo::factory()->create();
        WorkflowService::criarFluxo($aditivo, $user, '127.0.0.1');

        $etapaAtual = WorkflowService::obterEtapaAtual($aditivo);
        $resultado = WorkflowService::aprovar($etapaAtual, $user, 'Aprovado.', '127.0.0.1');

        $this->assertEquals(StatusAprovacao::Aprovado, $resultado->status);
        $this->assertNotNull($resultado->decided_at);
    }

    public function test_aprovar_etapa_fora_de_ordem_lanca_exception(): void
    {
        $user = $this->createAdminUser();
        $aditivo = Aditivo::factory()->create();
        WorkflowService::criarFluxo($aditivo, $user, '127.0.0.1');

        // Pega a terceira etapa (ordem 3) — a etapa 2 ainda esta pendente
        $etapa3 = WorkflowAprovacao::where('aprovavel_type', $aditivo->getMorphClass())
            ->where('aprovavel_id', $aditivo->id)
            ->where('etapa_ordem', 3)
            ->first();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('RN-337');

        WorkflowService::aprovar($etapa3, $user, 'Tentativa', '127.0.0.1');
    }

    public function test_aprovar_etapa_ja_processada_lanca_exception(): void
    {
        $user = $this->createAdminUser();
        $aditivo = Aditivo::factory()->create();
        WorkflowService::criarFluxo($aditivo, $user, '127.0.0.1');

        // Etapa 1 ja foi auto-aprovada
        $etapa1 = WorkflowAprovacao::where('aprovavel_type', $aditivo->getMorphClass())
            ->where('aprovavel_id', $aditivo->id)
            ->where('etapa_ordem', 1)
            ->first();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('ja foi processada');

        WorkflowService::aprovar($etapa1, $user, 'Tentativa', '127.0.0.1');
    }

    // --- reprovar ---

    public function test_reprovar_com_parecer(): void
    {
        $user = $this->createAdminUser();
        $aditivo = Aditivo::factory()->create();
        WorkflowService::criarFluxo($aditivo, $user, '127.0.0.1');

        $etapaAtual = WorkflowService::obterEtapaAtual($aditivo);
        $resultado = WorkflowService::reprovar($etapaAtual, $user, 'Documentacao insuficiente.', '127.0.0.1');

        $this->assertEquals(StatusAprovacao::Reprovado, $resultado->status);
        $this->assertEquals('Documentacao insuficiente.', $resultado->parecer);
        $this->assertNotNull($resultado->decided_at);
    }

    public function test_reprovar_sem_parecer_lanca_exception(): void
    {
        $user = $this->createAdminUser();
        $aditivo = Aditivo::factory()->create();
        WorkflowService::criarFluxo($aditivo, $user, '127.0.0.1');

        $etapaAtual = WorkflowService::obterEtapaAtual($aditivo);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('RN-338');

        WorkflowService::reprovar($etapaAtual, $user, '', '127.0.0.1');
    }

    // --- obterEtapaAtual ---

    public function test_obter_etapa_atual_retorna_pendente(): void
    {
        $user = $this->createAdminUser();
        $aditivo = Aditivo::factory()->create();
        WorkflowService::criarFluxo($aditivo, $user, '127.0.0.1');

        $etapaAtual = WorkflowService::obterEtapaAtual($aditivo);

        $this->assertNotNull($etapaAtual);
        $this->assertEquals(StatusAprovacao::Pendente, $etapaAtual->status);
        $this->assertEquals(2, $etapaAtual->etapa_ordem); // Etapa 1 auto-aprovada
    }

    public function test_obter_etapa_atual_nenhuma_pendente(): void
    {
        $user = $this->createAdminUser();
        $aditivo = Aditivo::factory()->create();
        WorkflowService::criarFluxo($aditivo, $user, '127.0.0.1');

        // Aprova todas as etapas pendentes
        for ($i = 0; $i < 4; $i++) {
            $etapa = WorkflowService::obterEtapaAtual($aditivo);
            if ($etapa) {
                WorkflowService::aprovar($etapa, $user, 'OK', '127.0.0.1');
            }
        }

        $etapaAtual = WorkflowService::obterEtapaAtual($aditivo);

        $this->assertNull($etapaAtual);
    }

    // --- isAprovado ---

    public function test_is_aprovado_todas_etapas_aprovadas(): void
    {
        $user = $this->createAdminUser();
        $aditivo = Aditivo::factory()->create();
        WorkflowService::criarFluxo($aditivo, $user, '127.0.0.1');

        // Aprova todas as etapas pendentes
        for ($i = 0; $i < 4; $i++) {
            $etapa = WorkflowService::obterEtapaAtual($aditivo);
            if ($etapa) {
                WorkflowService::aprovar($etapa, $user, 'OK', '127.0.0.1');
            }
        }

        $this->assertTrue(WorkflowService::isAprovado($aditivo));
    }

    public function test_is_aprovado_parcialmente_retorna_false(): void
    {
        $user = $this->createAdminUser();
        $aditivo = Aditivo::factory()->create();
        WorkflowService::criarFluxo($aditivo, $user, '127.0.0.1');

        // Apenas etapa 1 auto-aprovada
        $this->assertFalse(WorkflowService::isAprovado($aditivo));
    }

    public function test_is_aprovado_sem_workflow_retorna_false(): void
    {
        $aditivo = Aditivo::factory()->create();

        $this->assertFalse(WorkflowService::isAprovado($aditivo));
    }

    // --- obterHistorico ---

    public function test_obter_historico_ordenado_por_etapa(): void
    {
        $user = $this->createAdminUser();
        $aditivo = Aditivo::factory()->create();
        WorkflowService::criarFluxo($aditivo, $user, '127.0.0.1');

        $historico = WorkflowService::obterHistorico($aditivo);

        $this->assertCount(5, $historico);
        $this->assertEquals(1, $historico->first()->etapa_ordem);
        $this->assertEquals(5, $historico->last()->etapa_ordem);
    }
}
