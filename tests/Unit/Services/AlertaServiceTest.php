<?php

namespace Tests\Unit\Services;

use App\Enums\CategoriaContrato;
use App\Enums\PrioridadeAlerta;
use App\Enums\StatusAlerta;
use App\Enums\StatusContrato;
use App\Enums\TipoEventoAlerta;
use App\Models\Alerta;
use App\Models\ConfiguracaoAlerta;
use App\Models\Contrato;
use App\Models\Fiscal;
use App\Models\Servidor;
use App\Models\User;
use App\Services\AlertaService;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;
use Tests\Traits\RunsTenantMigrations;
use Tests\Traits\SeedsTenantData;

class AlertaServiceTest extends TestCase
{
    use RunsTenantMigrations;
    use SeedsTenantData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpTenant();
        $this->seedBaseData();
        Queue::fake();
    }

    // --- determinarPrioridade ---

    public function test_determinar_prioridade_7_dias_urgente(): void
    {
        $prioridade = AlertaService::determinarPrioridade(7, CategoriaContrato::NaoEssencial);

        $this->assertEquals(PrioridadeAlerta::Urgente, $prioridade);
    }

    public function test_determinar_prioridade_5_dias_urgente(): void
    {
        $prioridade = AlertaService::determinarPrioridade(5, null);

        $this->assertEquals(PrioridadeAlerta::Urgente, $prioridade);
    }

    public function test_determinar_prioridade_30_dias_atencao(): void
    {
        $prioridade = AlertaService::determinarPrioridade(30, CategoriaContrato::NaoEssencial);

        $this->assertEquals(PrioridadeAlerta::Atencao, $prioridade);
    }

    public function test_determinar_prioridade_15_dias_atencao(): void
    {
        $prioridade = AlertaService::determinarPrioridade(15, null);

        $this->assertEquals(PrioridadeAlerta::Atencao, $prioridade);
    }

    public function test_determinar_prioridade_90_dias_informativo(): void
    {
        $prioridade = AlertaService::determinarPrioridade(90, CategoriaContrato::NaoEssencial);

        $this->assertEquals(PrioridadeAlerta::Informativo, $prioridade);
    }

    public function test_determinar_prioridade_essencial_eleva_informativo_para_atencao(): void
    {
        $prioridade = AlertaService::determinarPrioridade(90, CategoriaContrato::Essencial);

        $this->assertEquals(PrioridadeAlerta::Atencao, $prioridade);
    }

    public function test_determinar_prioridade_essencial_eleva_atencao_para_urgente(): void
    {
        $prioridade = AlertaService::determinarPrioridade(20, CategoriaContrato::Essencial);

        $this->assertEquals(PrioridadeAlerta::Urgente, $prioridade);
    }

    public function test_determinar_prioridade_essencial_urgente_permanece_urgente(): void
    {
        $prioridade = AlertaService::determinarPrioridade(5, CategoriaContrato::Essencial);

        $this->assertEquals(PrioridadeAlerta::Urgente, $prioridade);
    }

    // --- gerarAlerta ---

    public function test_gerar_alerta_novo_cria_corretamente(): void
    {
        $contrato = Contrato::factory()->vigente()->vencendoEm(20)->create();

        $alerta = AlertaService::gerarAlerta(
            $contrato,
            TipoEventoAlerta::VencimentoVigencia,
            30,
            20,
            $contrato->data_fim
        );

        $this->assertNotNull($alerta);
        $this->assertEquals($contrato->id, $alerta->contrato_id);
        $this->assertEquals(StatusAlerta::Pendente, $alerta->status);
        $this->assertEquals(20, $alerta->dias_para_vencimento);
    }

    public function test_gerar_alerta_dedup_nao_duplica(): void
    {
        $contrato = Contrato::factory()->vigente()->vencendoEm(20)->create();

        $alerta1 = AlertaService::gerarAlerta(
            $contrato,
            TipoEventoAlerta::VencimentoVigencia,
            30,
            20,
            $contrato->data_fim
        );

        $alerta2 = AlertaService::gerarAlerta(
            $contrato,
            TipoEventoAlerta::VencimentoVigencia,
            30,
            20,
            $contrato->data_fim
        );

        $this->assertNotNull($alerta1);
        $this->assertNull($alerta2);
    }

    public function test_gerar_alerta_tipo_diferente_permite(): void
    {
        $contrato = Contrato::factory()->vigente()->vencendoEm(20)->create();

        $alerta1 = AlertaService::gerarAlerta(
            $contrato,
            TipoEventoAlerta::VencimentoVigencia,
            30,
            20,
            $contrato->data_fim
        );

        $alerta2 = AlertaService::gerarAlerta(
            $contrato,
            TipoEventoAlerta::TerminoAditivo,
            30,
            20,
            $contrato->data_fim
        );

        $this->assertNotNull($alerta1);
        $this->assertNotNull($alerta2);
    }

    // --- resolverAlertasPorContrato ---

    public function test_resolver_alertas_por_contrato(): void
    {
        $contrato = Contrato::factory()->vigente()->create();
        Alerta::factory()->create([
            'contrato_id' => $contrato->id,
            'status' => StatusAlerta::Pendente,
        ]);
        Alerta::factory()->create([
            'contrato_id' => $contrato->id,
            'status' => StatusAlerta::Enviado,
        ]);

        $resolvidos = AlertaService::resolverAlertasPorContrato($contrato);

        $this->assertEquals(2, $resolvidos);
        $this->assertEquals(0, Alerta::where('contrato_id', $contrato->id)->pendentes()->count());
    }

    public function test_resolver_alertas_nao_afeta_ja_resolvidos(): void
    {
        $contrato = Contrato::factory()->vigente()->create();
        Alerta::factory()->resolvido()->create(['contrato_id' => $contrato->id]);

        $resolvidos = AlertaService::resolverAlertasPorContrato($contrato);

        $this->assertEquals(0, $resolvidos);
    }

    // --- resolverManualmente ---

    public function test_resolver_manualmente(): void
    {
        $user = $this->createAdminUser();
        $alerta = Alerta::factory()->create(['status' => StatusAlerta::Pendente]);

        $resultado = AlertaService::resolverManualmente($alerta, $user);

        $this->assertEquals(StatusAlerta::Resolvido, $resultado->status);
        $this->assertEquals($user->id, $resultado->resolvido_por);
        $this->assertNotNull($resultado->resolvido_em);
    }

    public function test_resolver_manualmente_ja_resolvido_lanca_exception(): void
    {
        $user = $this->createAdminUser();
        $alerta = Alerta::factory()->resolvido()->create();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('ja foi resolvido');

        AlertaService::resolverManualmente($alerta, $user);
    }

    // --- marcarVisualizado ---

    public function test_marcar_visualizado_pendente(): void
    {
        $user = $this->createAdminUser();
        $alerta = Alerta::factory()->create(['status' => StatusAlerta::Pendente]);

        AlertaService::marcarVisualizado($alerta, $user);
        $alerta->refresh();

        $this->assertEquals(StatusAlerta::Visualizado, $alerta->status);
        $this->assertEquals($user->id, $alerta->visualizado_por);
    }

    public function test_marcar_visualizado_enviado(): void
    {
        $user = $this->createAdminUser();
        $alerta = Alerta::factory()->enviado()->create();

        AlertaService::marcarVisualizado($alerta, $user);
        $alerta->refresh();

        $this->assertEquals(StatusAlerta::Visualizado, $alerta->status);
    }

    public function test_marcar_visualizado_ja_resolvido_nao_altera(): void
    {
        $user = $this->createAdminUser();
        $alerta = Alerta::factory()->resolvido()->create();

        AlertaService::marcarVisualizado($alerta, $user);
        $alerta->refresh();

        $this->assertEquals(StatusAlerta::Resolvido, $alerta->status);
    }

    // --- obterDestinatarios ---

    public function test_obter_destinatarios_inclui_controladoria(): void
    {
        $contrato = Contrato::factory()->vigente()->create();
        $this->createUserWithRole('controladoria');

        $destinatarios = AlertaService::obterDestinatarios($contrato);

        $tipos = array_column($destinatarios, 'tipo');
        $this->assertContains('controlador', $tipos);
    }

    public function test_obter_destinatarios_essencial_inclui_admin(): void
    {
        $contrato = Contrato::factory()->vigente()->essencial()->create();
        $this->createAdminUser();
        $this->createUserWithRole('controladoria');

        $destinatarios = AlertaService::obterDestinatarios($contrato);

        $tipos = array_column($destinatarios, 'tipo');
        $this->assertContains('administrador', $tipos);
    }

    public function test_obter_destinatarios_nao_essencial_exclui_admin(): void
    {
        $contrato = Contrato::factory()->vigente()->create([
            'categoria' => CategoriaContrato::NaoEssencial,
        ]);
        $this->createAdminUser();

        $destinatarios = AlertaService::obterDestinatarios($contrato);

        $tipos = array_column($destinatarios, 'tipo');
        $this->assertNotContains('administrador', $tipos);
    }

    public function test_obter_destinatarios_dedup_por_email(): void
    {
        $contrato = Contrato::factory()->vigente()->essencial()->create();
        // O mesmo usuario e admin e controladoria nao deve acontecer,
        // mas usuarios com mesmo email sao deduplicados
        $user = $this->createAdminUser(['email' => 'unico@teste.com']);
        $this->createUserWithRole('controladoria', ['email' => 'outro@teste.com']);

        $destinatarios = AlertaService::obterDestinatarios($contrato);
        $emails = array_column($destinatarios, 'email');

        $this->assertEquals(count($emails), count(array_unique($emails)));
    }

    // --- gerarIndicadoresDashboard ---

    public function test_gerar_indicadores_dashboard(): void
    {
        Contrato::factory()->vigente()->vencendoEm(15)->create();
        Contrato::factory()->vigente()->vencendoEm(45)->create();
        Contrato::factory()->vigente()->vencendoEm(100)->create();
        Contrato::factory()->vencido()->create();

        $indicadores = AlertaService::gerarIndicadoresDashboard();

        $this->assertArrayHasKey('vencendo_120d', $indicadores);
        $this->assertArrayHasKey('vencendo_60d', $indicadores);
        $this->assertArrayHasKey('vencendo_30d', $indicadores);
        $this->assertArrayHasKey('vencidos', $indicadores);
        $this->assertGreaterThanOrEqual(1, $indicadores['vencidos']);
        $this->assertGreaterThanOrEqual(1, $indicadores['vencendo_30d']);
    }

    public function test_gerar_indicadores_dashboard_retorna_chaves_corretas(): void
    {
        $indicadores = AlertaService::gerarIndicadoresDashboard();

        $this->assertArrayHasKey('vencendo_120d', $indicadores);
        $this->assertArrayHasKey('vencendo_60d', $indicadores);
        $this->assertArrayHasKey('vencendo_30d', $indicadores);
        $this->assertArrayHasKey('vencidos', $indicadores);
        $this->assertIsInt($indicadores['vencendo_120d']);
        $this->assertIsInt($indicadores['vencidos']);
    }

    // --- verificarVencimentos ---

    public function test_verificar_vencimentos_marca_contratos_vencidos(): void
    {
        // Contrato com data_fim no passado mas status vigente
        Contrato::factory()->create([
            'status' => StatusContrato::Vigente,
            'data_fim' => now()->subDays(5)->format('Y-m-d'),
        ]);

        ConfiguracaoAlerta::updateOrCreate(
            ['dias_antecedencia' => 30],
            ['prioridade_padrao' => PrioridadeAlerta::Atencao->value, 'is_ativo' => true]
        );

        $resultado = AlertaService::verificarVencimentos();

        $this->assertGreaterThanOrEqual(1, $resultado['contratos_vencidos']);
    }

    public function test_verificar_vencimentos_gera_alertas(): void
    {
        // Contrato vencendo em 20 dias
        Contrato::factory()->vigente()->vencendoEm(20)->create();

        ConfiguracaoAlerta::updateOrCreate(
            ['dias_antecedencia' => 30],
            ['prioridade_padrao' => PrioridadeAlerta::Atencao->value, 'is_ativo' => true]
        );

        $resultado = AlertaService::verificarVencimentos();

        $this->assertGreaterThanOrEqual(1, $resultado['alertas_gerados']);
    }
}
