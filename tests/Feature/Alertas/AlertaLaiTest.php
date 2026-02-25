<?php

namespace Tests\Feature\Alertas;

use App\Enums\ClassificacaoSigilo;
use App\Enums\PrioridadeAlerta;
use App\Enums\StatusContrato;
use App\Enums\StatusSolicitacaoLai;
use App\Enums\TipoEventoAlerta;
use App\Models\Alerta;
use App\Models\Contrato;
use App\Models\SolicitacaoLai;
use App\Models\Tenant;
use App\Models\User;
use App\Services\AlertaService;
use App\Services\DashboardService;
use App\Services\PublicacaoAutomaticaService;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;
use Tests\Traits\RunsTenantMigrations;
use Tests\Traits\SeedsTenantData;

class AlertaLaiTest extends TestCase
{
    use RunsTenantMigrations;
    use SeedsTenantData;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpTenant();
        $this->seedBaseData();
        $this->admin = $this->createAdminUser();
        Queue::fake();
    }

    // ═══════════════════════════════════════════════════════════
    // ENUM: TipoEventoAlerta — 4 novos cases LAI
    // ═══════════════════════════════════════════════════════════

    public function test_tipo_evento_alerta_contem_cases_lai(): void
    {
        $cases = TipoEventoAlerta::cases();

        $this->assertContains(TipoEventoAlerta::ContratoNaoPublicadoPortal, $cases);
        $this->assertContains(TipoEventoAlerta::SolicitacaoLaiVencendo, $cases);
        $this->assertContains(TipoEventoAlerta::SolicitacaoLaiVencida, $cases);
        $this->assertContains(TipoEventoAlerta::SigiloSemJustificativa, $cases);
        $this->assertCount(18, $cases);
    }

    public function test_labels_lai_corretos(): void
    {
        $this->assertEquals('Contrato Nao Publicado no Portal', TipoEventoAlerta::ContratoNaoPublicadoPortal->label());
        $this->assertEquals('Solicitacao LAI Vencendo', TipoEventoAlerta::SolicitacaoLaiVencendo->label());
        $this->assertEquals('Solicitacao LAI Vencida', TipoEventoAlerta::SolicitacaoLaiVencida->label());
        $this->assertEquals('Sigilo sem Justificativa', TipoEventoAlerta::SigiloSemJustificativa->label());
    }

    public function test_severidades_lai_corretas(): void
    {
        $this->assertEquals('padrao', TipoEventoAlerta::ContratoNaoPublicadoPortal->severidade());
        $this->assertEquals('media', TipoEventoAlerta::SolicitacaoLaiVencendo->severidade());
        $this->assertEquals('alta', TipoEventoAlerta::SolicitacaoLaiVencida->severidade());
        $this->assertEquals('media', TipoEventoAlerta::SigiloSemJustificativa->severidade());
    }

    // ═══════════════════════════════════════════════════════════
    // verificarContratosNaoPublicados
    // ═══════════════════════════════════════════════════════════

    public function test_contrato_publico_nao_publicado_gera_alerta(): void
    {
        $contrato = Contrato::factory()->create([
            'status' => StatusContrato::Vigente->value,
            'classificacao_sigilo' => ClassificacaoSigilo::Publico->value,
            'publicado_portal' => false,
            'data_inicio' => now()->subMonths(6),
            'data_fim' => now()->addMonths(6),
        ]);

        $alertas = AlertaService::verificarContratosNaoPublicados();

        $this->assertGreaterThanOrEqual(1, $alertas);
        $this->assertDatabaseHas('alertas', [
            'contrato_id' => $contrato->id,
            'tipo_evento' => TipoEventoAlerta::ContratoNaoPublicadoPortal->value,
        ]);
    }

    public function test_contrato_ja_publicado_nao_gera_alerta(): void
    {
        Contrato::factory()->create([
            'status' => StatusContrato::Vigente->value,
            'classificacao_sigilo' => ClassificacaoSigilo::Publico->value,
            'publicado_portal' => true,
            'data_inicio' => now()->subMonths(6),
            'data_fim' => now()->addMonths(6),
        ]);

        $alertas = AlertaService::verificarContratosNaoPublicados();

        $this->assertEquals(0, $alertas);
    }

    public function test_contrato_sigiloso_nao_gera_alerta_publicacao(): void
    {
        Contrato::factory()->create([
            'status' => StatusContrato::Vigente->value,
            'classificacao_sigilo' => ClassificacaoSigilo::Reservado->value,
            'publicado_portal' => false,
            'justificativa_sigilo' => 'Informacao estrategica de seguranca publica',
            'data_inicio' => now()->subMonths(6),
            'data_fim' => now()->addMonths(6),
        ]);

        $alertas = AlertaService::verificarContratosNaoPublicados();

        $this->assertEquals(0, $alertas);
    }

    public function test_deduplicacao_contrato_nao_publicado(): void
    {
        Contrato::factory()->create([
            'status' => StatusContrato::Vigente->value,
            'classificacao_sigilo' => ClassificacaoSigilo::Publico->value,
            'publicado_portal' => false,
            'data_inicio' => now()->subMonths(6),
            'data_fim' => now()->addMonths(6),
        ]);

        $primeira = AlertaService::verificarContratosNaoPublicados();
        $segunda = AlertaService::verificarContratosNaoPublicados();

        $this->assertGreaterThanOrEqual(1, $primeira);
        $this->assertEquals(0, $segunda);
    }

    // ═══════════════════════════════════════════════════════════
    // verificarSigiloSemJustificativa
    // ═══════════════════════════════════════════════════════════

    public function test_contrato_reservado_sem_justificativa_gera_alerta(): void
    {
        $contrato = Contrato::factory()->create([
            'status' => StatusContrato::Vigente->value,
            'classificacao_sigilo' => ClassificacaoSigilo::Reservado->value,
            'justificativa_sigilo' => null,
            'data_inicio' => now()->subMonths(3),
            'data_fim' => now()->addMonths(9),
        ]);

        $alertas = AlertaService::verificarSigiloSemJustificativa();

        $this->assertGreaterThanOrEqual(1, $alertas);
        $this->assertDatabaseHas('alertas', [
            'contrato_id' => $contrato->id,
            'tipo_evento' => TipoEventoAlerta::SigiloSemJustificativa->value,
        ]);
    }

    public function test_contrato_reservado_com_justificativa_nao_gera(): void
    {
        Contrato::factory()->create([
            'status' => StatusContrato::Vigente->value,
            'classificacao_sigilo' => ClassificacaoSigilo::Reservado->value,
            'justificativa_sigilo' => 'Contrato envolve estrategia de seguranca publica conforme Lei 12.527 art. 23',
            'data_inicio' => now()->subMonths(3),
            'data_fim' => now()->addMonths(9),
        ]);

        $alertas = AlertaService::verificarSigiloSemJustificativa();

        $this->assertEquals(0, $alertas);
    }

    public function test_contrato_publico_nao_gera_alerta_justificativa(): void
    {
        Contrato::factory()->create([
            'status' => StatusContrato::Vigente->value,
            'classificacao_sigilo' => ClassificacaoSigilo::Publico->value,
            'justificativa_sigilo' => null,
            'publicado_portal' => true,
            'data_inicio' => now()->subMonths(3),
            'data_fim' => now()->addMonths(9),
        ]);

        $alertas = AlertaService::verificarSigiloSemJustificativa();

        $this->assertEquals(0, $alertas);
    }

    public function test_deduplicacao_sigilo_sem_justificativa(): void
    {
        Contrato::factory()->create([
            'status' => StatusContrato::Vigente->value,
            'classificacao_sigilo' => ClassificacaoSigilo::Secreto->value,
            'justificativa_sigilo' => null,
            'data_inicio' => now()->subMonths(3),
            'data_fim' => now()->addMonths(9),
        ]);

        $primeira = AlertaService::verificarSigiloSemJustificativa();
        $segunda = AlertaService::verificarSigiloSemJustificativa();

        $this->assertGreaterThanOrEqual(1, $primeira);
        $this->assertEquals(0, $segunda);
    }

    // ═══════════════════════════════════════════════════════════
    // PublicacaoAutomaticaService
    // ═══════════════════════════════════════════════════════════

    public function test_publica_contrato_publico_nao_publicado(): void
    {
        // Garantir que admin existe para auditoria
        $this->assertNotNull($this->admin->id);

        $contrato = Contrato::factory()->create([
            'status' => StatusContrato::Vigente->value,
            'classificacao_sigilo' => ClassificacaoSigilo::Publico->value,
            'data_publicacao' => now()->subDays(5)->format('Y-m-d'),
            'publicado_portal' => false,
            'data_inicio' => now()->subMonths(3),
            'data_fim' => now()->addMonths(9),
        ]);

        $resultado = PublicacaoAutomaticaService::publicar();

        $this->assertEquals(1, $resultado['publicados']);
        $this->assertDatabaseHas('contratos', [
            'id' => $contrato->id,
            'publicado_portal' => true,
        ]);

        // Verifica auditoria
        $this->assertDatabaseHas('historico_alteracoes', [
            'auditable_type' => Contrato::class,
            'auditable_id' => $contrato->id,
            'campo_alterado' => 'publicado_portal',
            'valor_novo' => 'true',
            'role_nome' => 'sistema',
        ]);
    }

    public function test_nao_publica_contrato_sem_data_publicacao(): void
    {
        Contrato::factory()->create([
            'status' => StatusContrato::Vigente->value,
            'classificacao_sigilo' => ClassificacaoSigilo::Publico->value,
            'data_publicacao' => null,
            'publicado_portal' => false,
            'data_inicio' => now()->subMonths(3),
            'data_fim' => now()->addMonths(9),
        ]);

        $resultado = PublicacaoAutomaticaService::publicar();

        $this->assertEquals(0, $resultado['publicados']);
    }

    public function test_nao_publica_contrato_sigiloso(): void
    {
        Contrato::factory()->create([
            'status' => StatusContrato::Vigente->value,
            'classificacao_sigilo' => ClassificacaoSigilo::Reservado->value,
            'data_publicacao' => now()->subDays(5)->format('Y-m-d'),
            'publicado_portal' => false,
            'justificativa_sigilo' => 'Seguranca publica',
            'data_inicio' => now()->subMonths(3),
            'data_fim' => now()->addMonths(9),
        ]);

        $resultado = PublicacaoAutomaticaService::publicar();

        $this->assertEquals(0, $resultado['publicados']);
    }

    public function test_nao_republica_ja_publicado(): void
    {
        Contrato::factory()->create([
            'status' => StatusContrato::Vigente->value,
            'classificacao_sigilo' => ClassificacaoSigilo::Publico->value,
            'data_publicacao' => now()->subDays(5)->format('Y-m-d'),
            'publicado_portal' => true,
            'data_inicio' => now()->subMonths(3),
            'data_fim' => now()->addMonths(9),
        ]);

        $resultado = PublicacaoAutomaticaService::publicar();

        $this->assertEquals(0, $resultado['publicados']);
        $this->assertGreaterThanOrEqual(1, $resultado['ja_publicados']);
    }

    // ═══════════════════════════════════════════════════════════
    // Dashboard indicadores LAI
    // ═══════════════════════════════════════════════════════════

    public function test_indicadores_lai_retorna_estrutura(): void
    {
        $indicadores = DashboardService::indicadoresLai();

        $this->assertArrayHasKey('solicitacoes_pendentes', $indicadores);
        $this->assertArrayHasKey('solicitacoes_vencidas', $indicadores);
        $this->assertArrayHasKey('contratos_nao_publicados', $indicadores);
        $this->assertArrayHasKey('tempo_medio_resposta', $indicadores);
    }

    public function test_indicadores_lai_conta_pendentes_e_vencidas(): void
    {
        // Solicitacao pendente (prazo futuro)
        SolicitacaoLai::create([
            'protocolo' => 'LAI-2025-000001',
            'nome_solicitante' => 'Cidadao Teste',
            'email_solicitante' => 'cidadao@test.com',
            'cpf_solicitante' => '12345678901',
            'assunto' => 'Teste pendente',
            'descricao' => 'Descricao do pedido LAI',
            'status' => StatusSolicitacaoLai::Recebida->value,
            'prazo_legal' => now()->addDays(10)->format('Y-m-d'),
            'tenant_id' => 1,
        ]);

        // Solicitacao vencida (prazo passado)
        SolicitacaoLai::create([
            'protocolo' => 'LAI-2025-000002',
            'nome_solicitante' => 'Cidadao Dois',
            'email_solicitante' => 'cidadao2@test.com',
            'cpf_solicitante' => '98765432100',
            'assunto' => 'Teste vencida',
            'descricao' => 'Descricao do pedido LAI vencido',
            'status' => StatusSolicitacaoLai::Recebida->value,
            'prazo_legal' => now()->subDays(5)->format('Y-m-d'),
            'tenant_id' => 1,
        ]);

        // Solicitacao respondida (nao pendente)
        SolicitacaoLai::create([
            'protocolo' => 'LAI-2025-000003',
            'nome_solicitante' => 'Cidadao Tres',
            'email_solicitante' => 'cidadao3@test.com',
            'cpf_solicitante' => '11122233344',
            'assunto' => 'Teste respondida',
            'descricao' => 'Descricao do pedido LAI respondido',
            'status' => StatusSolicitacaoLai::Respondida->value,
            'prazo_legal' => now()->subDays(15)->format('Y-m-d'),
            'data_resposta' => now()->subDays(3),
            'tenant_id' => 1,
        ]);

        $indicadores = DashboardService::indicadoresLai();

        $this->assertEquals(2, $indicadores['solicitacoes_pendentes']); // recebida + vencida (ambas pendentes)
        $this->assertEquals(1, $indicadores['solicitacoes_vencidas']); // apenas a vencida
    }

    public function test_indicadores_lai_conta_nao_publicados(): void
    {
        // Baseline antes de criar dados de teste
        $antes = DashboardService::indicadoresLai()['contratos_nao_publicados'];

        // Contrato publico nao publicado
        Contrato::factory()->create([
            'status' => StatusContrato::Vigente->value,
            'classificacao_sigilo' => ClassificacaoSigilo::Publico->value,
            'publicado_portal' => false,
            'data_inicio' => now()->subMonths(3),
            'data_fim' => now()->addMonths(9),
        ]);

        // Contrato publico ja publicado (nao deve contar)
        Contrato::factory()->create([
            'status' => StatusContrato::Vigente->value,
            'classificacao_sigilo' => ClassificacaoSigilo::Publico->value,
            'publicado_portal' => true,
            'data_inicio' => now()->subMonths(3),
            'data_fim' => now()->addMonths(9),
        ]);

        $indicadores = DashboardService::indicadoresLai();

        $this->assertEquals($antes + 1, $indicadores['contratos_nao_publicados']);
    }

    // ═══════════════════════════════════════════════════════════
    // Integracao com verificarVencimentos
    // ═══════════════════════════════════════════════════════════

    public function test_verificar_vencimentos_inclui_alertas_lai(): void
    {
        // Contrato publico nao publicado (gera alerta ContratoNaoPublicadoPortal)
        $contrato1 = Contrato::factory()->create([
            'status' => StatusContrato::Vigente->value,
            'classificacao_sigilo' => ClassificacaoSigilo::Publico->value,
            'publicado_portal' => false,
            'data_inicio' => now()->subMonths(3),
            'data_fim' => now()->addMonths(9),
        ]);

        // Contrato reservado sem justificativa (gera alerta SigiloSemJustificativa)
        $contrato2 = Contrato::factory()->create([
            'status' => StatusContrato::Vigente->value,
            'classificacao_sigilo' => ClassificacaoSigilo::Reservado->value,
            'justificativa_sigilo' => null,
            'data_inicio' => now()->subMonths(3),
            'data_fim' => now()->addMonths(9),
        ]);

        $resultado = AlertaService::verificarVencimentos();

        $this->assertGreaterThanOrEqual(2, $resultado['alertas_gerados']);

        $this->assertDatabaseHas('alertas', [
            'contrato_id' => $contrato1->id,
            'tipo_evento' => TipoEventoAlerta::ContratoNaoPublicadoPortal->value,
        ]);

        $this->assertDatabaseHas('alertas', [
            'contrato_id' => $contrato2->id,
            'tipo_evento' => TipoEventoAlerta::SigiloSemJustificativa->value,
        ]);
    }

    // ═══════════════════════════════════════════════════════════
    // Command lai:publicar-automatico
    // ═══════════════════════════════════════════════════════════

    public function test_command_publicar_automatico_executa(): void
    {
        // Nota: DB::purge quebra transacao — testar via output
        $this->artisan('lai:publicar-automatico')
            ->assertExitCode(0);
    }
}
