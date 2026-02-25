<?php

namespace Tests\Feature\Compliance;

use App\Enums\ClassificacaoRespostaLai;
use App\Enums\StatusSolicitacaoLai;
use App\Models\HistoricoSolicitacaoLai;
use App\Models\SolicitacaoLai;
use App\Models\User;
use App\Services\SolicitacaoLaiService;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use Tests\Traits\RunsTenantMigrations;
use Tests\Traits\SeedsTenantData;

class SolicitacaoLaiTest extends TestCase
{
    use RunsTenantMigrations, SeedsTenantData;

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

    // ═══════════════════════════════════════════════════════════════
    // Enum StatusSolicitacaoLai
    // ═══════════════════════════════════════════════════════════════

    public function test_enum_status_tem_6_cases(): void
    {
        $this->assertCount(6, StatusSolicitacaoLai::cases());
    }

    public function test_enum_status_labels_e_cores(): void
    {
        $this->assertEquals('Recebida', StatusSolicitacaoLai::Recebida->label());
        $this->assertEquals('Em Analise', StatusSolicitacaoLai::EmAnalise->label());
        $this->assertEquals('Respondida', StatusSolicitacaoLai::Respondida->label());
        $this->assertEquals('Prorrogada', StatusSolicitacaoLai::Prorrogada->label());
        $this->assertEquals('Indeferida', StatusSolicitacaoLai::Indeferida->label());
        $this->assertEquals('Em Recurso', StatusSolicitacaoLai::Recurso->label());

        foreach (StatusSolicitacaoLai::cases() as $case) {
            $this->assertNotEmpty($case->cor());
            $this->assertNotEmpty($case->icone());
        }
    }

    public function test_enum_status_is_finalizado(): void
    {
        $this->assertFalse(StatusSolicitacaoLai::Recebida->isFinalizado());
        $this->assertFalse(StatusSolicitacaoLai::EmAnalise->isFinalizado());
        $this->assertTrue(StatusSolicitacaoLai::Respondida->isFinalizado());
        $this->assertFalse(StatusSolicitacaoLai::Prorrogada->isFinalizado());
        $this->assertTrue(StatusSolicitacaoLai::Indeferida->isFinalizado());
        $this->assertFalse(StatusSolicitacaoLai::Recurso->isFinalizado());
    }

    public function test_enum_status_permite_resposta_e_prorrogacao(): void
    {
        $this->assertTrue(StatusSolicitacaoLai::Recebida->permiteResposta());
        $this->assertTrue(StatusSolicitacaoLai::EmAnalise->permiteResposta());
        $this->assertTrue(StatusSolicitacaoLai::Prorrogada->permiteResposta());
        $this->assertFalse(StatusSolicitacaoLai::Respondida->permiteResposta());
        $this->assertFalse(StatusSolicitacaoLai::Indeferida->permiteResposta());

        $this->assertTrue(StatusSolicitacaoLai::Recebida->permiteProrrogacao());
        $this->assertTrue(StatusSolicitacaoLai::EmAnalise->permiteProrrogacao());
        $this->assertFalse(StatusSolicitacaoLai::Prorrogada->permiteProrrogacao());
        $this->assertFalse(StatusSolicitacaoLai::Respondida->permiteProrrogacao());
    }

    // ═══════════════════════════════════════════════════════════════
    // Enum ClassificacaoRespostaLai
    // ═══════════════════════════════════════════════════════════════

    public function test_enum_classificacao_resposta_tem_3_cases(): void
    {
        $this->assertCount(3, ClassificacaoRespostaLai::cases());
        $this->assertEquals('Deferida', ClassificacaoRespostaLai::Deferida->label());
        $this->assertEquals('Parcialmente Deferida', ClassificacaoRespostaLai::ParcialmenteDeferida->label());
        $this->assertEquals('Indeferida', ClassificacaoRespostaLai::Indeferida->label());

        foreach (ClassificacaoRespostaLai::cases() as $case) {
            $this->assertNotEmpty($case->cor());
            $this->assertNotEmpty($case->icone());
        }
    }

    // ═══════════════════════════════════════════════════════════════
    // Model SolicitacaoLai
    // ═══════════════════════════════════════════════════════════════

    public function test_model_fillable_e_casts(): void
    {
        $solicitacao = SolicitacaoLai::create([
            'protocolo' => 'LAI-2026-999001',
            'nome_solicitante' => 'Teste Cidadao',
            'email_solicitante' => 'teste@email.com',
            'cpf_solicitante' => '123.456.789-00',
            'assunto' => 'Assunto de teste',
            'descricao' => 'Descricao detalhada do teste',
            'status' => StatusSolicitacaoLai::Recebida->value,
            'prazo_legal' => now()->addDays(20)->toDateString(),
            'tenant_id' => app('tenant')->id,
        ]);

        $solicitacao->refresh();

        $this->assertEquals(StatusSolicitacaoLai::Recebida, $solicitacao->status);
        $this->assertInstanceOf(\Carbon\Carbon::class, $solicitacao->prazo_legal);
        // CPF criptografado: valor recuperado via cast encrypted
        $this->assertEquals('123.456.789-00', $solicitacao->cpf_solicitante);
    }

    public function test_model_scope_pendentes(): void
    {
        SolicitacaoLai::create([
            'protocolo' => 'LAI-2026-990001',
            'nome_solicitante' => 'Pendente 1',
            'email_solicitante' => 'p1@e.com',
            'cpf_solicitante' => '111.111.111-11',
            'assunto' => 'Pendente',
            'descricao' => 'Descricao pendente de teste',
            'status' => StatusSolicitacaoLai::Recebida->value,
            'prazo_legal' => now()->addDays(20)->toDateString(),
            'tenant_id' => app('tenant')->id,
        ]);

        SolicitacaoLai::create([
            'protocolo' => 'LAI-2026-990002',
            'nome_solicitante' => 'Respondida 1',
            'email_solicitante' => 'r1@e.com',
            'cpf_solicitante' => '222.222.222-22',
            'assunto' => 'Respondida',
            'descricao' => 'Descricao respondida de teste',
            'status' => StatusSolicitacaoLai::Respondida->value,
            'prazo_legal' => now()->addDays(20)->toDateString(),
            'tenant_id' => app('tenant')->id,
        ]);

        $pendentes = SolicitacaoLai::pendentes()
            ->whereIn('protocolo', ['LAI-2026-990001', 'LAI-2026-990002'])
            ->count();
        $this->assertEquals(1, $pendentes);
    }

    public function test_model_scope_vencidas(): void
    {
        // Vencida: pendente + prazo expirado
        SolicitacaoLai::create([
            'protocolo' => 'LAI-2026-980001',
            'nome_solicitante' => 'Vencida',
            'email_solicitante' => 'v@e.com',
            'cpf_solicitante' => '333.333.333-33',
            'assunto' => 'Vencida',
            'descricao' => 'Descricao vencida de teste',
            'status' => StatusSolicitacaoLai::EmAnalise->value,
            'prazo_legal' => now()->subDays(5)->toDateString(),
            'tenant_id' => app('tenant')->id,
        ]);

        // Nao vencida: prazo futuro
        SolicitacaoLai::create([
            'protocolo' => 'LAI-2026-980002',
            'nome_solicitante' => 'No Prazo',
            'email_solicitante' => 'np@e.com',
            'cpf_solicitante' => '444.444.444-44',
            'assunto' => 'No Prazo',
            'descricao' => 'Descricao no prazo de teste',
            'status' => StatusSolicitacaoLai::EmAnalise->value,
            'prazo_legal' => now()->addDays(10)->toDateString(),
            'tenant_id' => app('tenant')->id,
        ]);

        $this->assertEquals(1, SolicitacaoLai::vencidas()->count());
    }

    public function test_model_accessors_dias_restantes_e_vencida(): void
    {
        $solicitacao = SolicitacaoLai::create([
            'protocolo' => 'LAI-2026-970001',
            'nome_solicitante' => 'Accessor Test',
            'email_solicitante' => 'at@e.com',
            'cpf_solicitante' => '555.555.555-55',
            'assunto' => 'Accessor',
            'descricao' => 'Descricao de teste accessor',
            'status' => StatusSolicitacaoLai::Recebida->value,
            'prazo_legal' => now()->addDays(10)->toDateString(),
            'tenant_id' => app('tenant')->id,
        ]);

        $this->assertEquals(10, $solicitacao->dias_restantes);
        $this->assertFalse($solicitacao->is_vencida);

        // Vencida
        $solicitacao->prazo_legal = now()->subDays(3)->toDateString();
        $solicitacao->save();
        $solicitacao->refresh();

        $this->assertEquals(-3, $solicitacao->dias_restantes);
        $this->assertTrue($solicitacao->is_vencida);
    }

    public function test_model_accessor_is_prorrogavel(): void
    {
        $solicitacao = SolicitacaoLai::create([
            'protocolo' => 'LAI-2026-960001',
            'nome_solicitante' => 'Prorrogavel Test',
            'email_solicitante' => 'pt@e.com',
            'cpf_solicitante' => '666.666.666-66',
            'assunto' => 'Prorrogavel',
            'descricao' => 'Descricao de teste prorrogavel',
            'status' => StatusSolicitacaoLai::EmAnalise->value,
            'prazo_legal' => now()->addDays(10)->toDateString(),
            'tenant_id' => app('tenant')->id,
        ]);

        $this->assertTrue($solicitacao->is_prorrogavel);

        // Apos prorrogacao
        $solicitacao->update(['prazo_estendido' => now()->addDays(20)->toDateString()]);
        $solicitacao->refresh();

        $this->assertFalse($solicitacao->is_prorrogavel);
    }

    // ═══════════════════════════════════════════════════════════════
    // Model HistoricoSolicitacaoLai — Append-only
    // ═══════════════════════════════════════════════════════════════

    public function test_historico_imutavel_update_bloqueado(): void
    {
        $solicitacao = SolicitacaoLai::create([
            'protocolo' => 'LAI-2026-950001',
            'nome_solicitante' => 'Historico Test',
            'email_solicitante' => 'ht@e.com',
            'cpf_solicitante' => '777.777.777-77',
            'assunto' => 'Historico',
            'descricao' => 'Descricao de teste historico',
            'status' => StatusSolicitacaoLai::Recebida->value,
            'prazo_legal' => now()->addDays(20)->toDateString(),
            'tenant_id' => app('tenant')->id,
        ]);

        $historico = HistoricoSolicitacaoLai::create([
            'solicitacao_lai_id' => $solicitacao->id,
            'status_anterior' => null,
            'status_novo' => StatusSolicitacaoLai::Recebida->value,
            'observacao' => 'Registro inicial',
            'created_at' => now(),
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('imutavel');

        $historico->update(['observacao' => 'Tentativa de alteracao']);
    }

    public function test_historico_imutavel_delete_bloqueado(): void
    {
        $solicitacao = SolicitacaoLai::create([
            'protocolo' => 'LAI-2026-940001',
            'nome_solicitante' => 'Historico Delete',
            'email_solicitante' => 'hd@e.com',
            'cpf_solicitante' => '888.888.888-88',
            'assunto' => 'Historico Delete',
            'descricao' => 'Descricao de teste historico delete',
            'status' => StatusSolicitacaoLai::Recebida->value,
            'prazo_legal' => now()->addDays(20)->toDateString(),
            'tenant_id' => app('tenant')->id,
        ]);

        $historico = HistoricoSolicitacaoLai::create([
            'solicitacao_lai_id' => $solicitacao->id,
            'status_anterior' => null,
            'status_novo' => StatusSolicitacaoLai::Recebida->value,
            'created_at' => now(),
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('excluido');

        $historico->delete();
    }

    // ═══════════════════════════════════════════════════════════════
    // Service — SolicitacaoLaiService
    // ═══════════════════════════════════════════════════════════════

    public function test_service_criar_gera_protocolo_e_prazo(): void
    {
        $solicitacao = SolicitacaoLaiService::criar([
            'nome_solicitante' => 'Maria Silva',
            'email_solicitante' => 'maria@email.com',
            'cpf_solicitante' => '123.456.789-00',
            'assunto' => 'Informacao sobre contratos',
            'descricao' => 'Solicito informacoes detalhadas sobre contratos vigentes do municipio.',
        ]);

        $this->assertNotNull($solicitacao->id);
        $this->assertStringStartsWith('LAI-' . now()->year . '-', $solicitacao->protocolo);
        $this->assertEquals(StatusSolicitacaoLai::Recebida, $solicitacao->status);
        $this->assertEquals(now()->addDays(20)->toDateString(), $solicitacao->prazo_legal->toDateString());
        $this->assertNull($solicitacao->prazo_estendido);

        // Historico inicial criado
        $this->assertEquals(1, $solicitacao->historicos()->count());
        $historico = $solicitacao->historicos()->first();
        $this->assertNull($historico->status_anterior);
        $this->assertEquals('recebida', $historico->status_novo);
    }

    public function test_service_protocolo_sequencial(): void
    {
        $s1 = SolicitacaoLaiService::criar([
            'nome_solicitante' => 'Seq 1',
            'email_solicitante' => 's1@e.com',
            'cpf_solicitante' => '111.111.111-11',
            'assunto' => 'Sequencial 1',
            'descricao' => 'Teste de protocolo sequencial primeiro',
        ]);

        $s2 = SolicitacaoLaiService::criar([
            'nome_solicitante' => 'Seq 2',
            'email_solicitante' => 's2@e.com',
            'cpf_solicitante' => '222.222.222-22',
            'assunto' => 'Sequencial 2',
            'descricao' => 'Teste de protocolo sequencial segundo',
        ]);

        // Verifica que sao sequenciais
        $num1 = (int) substr($s1->protocolo, -6);
        $num2 = (int) substr($s2->protocolo, -6);
        $this->assertEquals($num1 + 1, $num2);
    }

    public function test_service_analisar(): void
    {
        $solicitacao = SolicitacaoLaiService::criar([
            'nome_solicitante' => 'Analisar Test',
            'email_solicitante' => 'analisar@e.com',
            'cpf_solicitante' => '111.000.111-00',
            'assunto' => 'Teste analisar',
            'descricao' => 'Descricao para teste de analise de solicitacao',
        ]);

        SolicitacaoLaiService::analisar($solicitacao, $this->admin, '127.0.0.1');

        $solicitacao->refresh();
        $this->assertEquals(StatusSolicitacaoLai::EmAnalise, $solicitacao->status);
        $this->assertEquals(2, $solicitacao->historicos()->count());
    }

    public function test_service_responder(): void
    {
        $solicitacao = SolicitacaoLaiService::criar([
            'nome_solicitante' => 'Responder Test',
            'email_solicitante' => 'resp@e.com',
            'cpf_solicitante' => '222.000.222-00',
            'assunto' => 'Teste responder',
            'descricao' => 'Descricao para teste de resposta de solicitacao',
        ]);

        SolicitacaoLaiService::responder(
            $solicitacao,
            'Segue a informacao solicitada com todos os detalhes necessarios.',
            ClassificacaoRespostaLai::Deferida,
            $this->admin,
            '127.0.0.1'
        );

        $solicitacao->refresh();
        $this->assertEquals(StatusSolicitacaoLai::Respondida, $solicitacao->status);
        $this->assertEquals(ClassificacaoRespostaLai::Deferida, $solicitacao->classificacao_resposta);
        $this->assertNotNull($solicitacao->data_resposta);
        $this->assertEquals($this->admin->id, $solicitacao->respondido_por);
        $this->assertStringContainsString('informacao solicitada', $solicitacao->resposta);
    }

    public function test_service_prorrogar_calcula_prazo_estendido(): void
    {
        $solicitacao = SolicitacaoLaiService::criar([
            'nome_solicitante' => 'Prorrogar Test',
            'email_solicitante' => 'prorrogar@e.com',
            'cpf_solicitante' => '333.000.333-00',
            'assunto' => 'Teste prorrogar',
            'descricao' => 'Descricao para teste de prorrogacao de solicitacao',
        ]);

        SolicitacaoLaiService::prorrogar(
            $solicitacao,
            'Necessidade de consolidar informacoes de multiplas secretarias para resposta completa.',
            $this->admin,
            '127.0.0.1'
        );

        $solicitacao->refresh();
        $this->assertEquals(StatusSolicitacaoLai::Prorrogada, $solicitacao->status);
        $this->assertNotNull($solicitacao->prazo_estendido);
        $this->assertNotNull($solicitacao->data_prorrogacao);

        // Prazo estendido = prazo_legal + 10 dias
        $expected = $solicitacao->prazo_legal->copy()->addDays(10)->toDateString();
        $this->assertEquals($expected, $solicitacao->prazo_estendido->toDateString());
    }

    public function test_service_prorrogar_max_uma_vez(): void
    {
        $solicitacao = SolicitacaoLaiService::criar([
            'nome_solicitante' => 'Prorrogar Dupla',
            'email_solicitante' => 'dupla@e.com',
            'cpf_solicitante' => '444.000.444-00',
            'assunto' => 'Teste prorrogacao duplicada',
            'descricao' => 'Descricao para teste de prorrogacao duplicada de solicitacao',
        ]);

        SolicitacaoLaiService::prorrogar(
            $solicitacao,
            'Justificativa primeira prorrogacao valida para teste.',
            $this->admin,
            '127.0.0.1'
        );

        $solicitacao->refresh();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('LAI art. 11');

        SolicitacaoLaiService::prorrogar(
            $solicitacao,
            'Segunda tentativa deve falhar conforme a lei.',
            $this->admin,
            '127.0.0.1'
        );
    }

    public function test_service_indeferir(): void
    {
        $solicitacao = SolicitacaoLaiService::criar([
            'nome_solicitante' => 'Indeferir Test',
            'email_solicitante' => 'indeferir@e.com',
            'cpf_solicitante' => '555.000.555-00',
            'assunto' => 'Teste indeferir',
            'descricao' => 'Descricao para teste de indeferimento de solicitacao',
        ]);

        SolicitacaoLaiService::indeferir(
            $solicitacao,
            'Informacao classificada como sigilosa conforme legislacao vigente.',
            ClassificacaoRespostaLai::Indeferida,
            $this->admin,
            '127.0.0.1'
        );

        $solicitacao->refresh();
        $this->assertEquals(StatusSolicitacaoLai::Indeferida, $solicitacao->status);
        $this->assertEquals(ClassificacaoRespostaLai::Indeferida, $solicitacao->classificacao_resposta);
        $this->assertNotNull($solicitacao->data_resposta);
    }

    public function test_service_resumo(): void
    {
        // Criar solicitacoes em diferentes status
        SolicitacaoLaiService::criar([
            'nome_solicitante' => 'Resumo 1',
            'email_solicitante' => 'r1@e.com',
            'cpf_solicitante' => '100.100.100-10',
            'assunto' => 'Resumo pendente',
            'descricao' => 'Descricao resumo pendente para teste completo',
        ]);

        $s2 = SolicitacaoLaiService::criar([
            'nome_solicitante' => 'Resumo 2',
            'email_solicitante' => 'r2@e.com',
            'cpf_solicitante' => '200.200.200-20',
            'assunto' => 'Resumo respondida',
            'descricao' => 'Descricao resumo respondida para teste completo',
        ]);

        SolicitacaoLaiService::responder(
            $s2,
            'Resposta para teste de resumo estatistico completo.',
            ClassificacaoRespostaLai::Deferida,
            $this->admin,
            '127.0.0.1'
        );

        $resumo = SolicitacaoLaiService::resumo();

        $this->assertArrayHasKey('total', $resumo);
        $this->assertArrayHasKey('pendentes', $resumo);
        $this->assertArrayHasKey('respondidas', $resumo);
        $this->assertArrayHasKey('vencidas', $resumo);
        $this->assertArrayHasKey('tempo_medio_resposta', $resumo);
        $this->assertGreaterThanOrEqual(2, $resumo['total']);
        $this->assertGreaterThanOrEqual(1, $resumo['pendentes']);
        $this->assertGreaterThanOrEqual(1, $resumo['respondidas']);
    }

    // ═══════════════════════════════════════════════════════════════
    // Controller Publico — SolicitacaoLaiPublicController
    // ═══════════════════════════════════════════════════════════════

    public function test_portal_lai_formulario_acessivel_sem_auth(): void
    {
        $response = $this->get("/{$this->tenant->slug}/portal/lai");
        $response->assertStatus(200);
        $response->assertSee('Solicitacao de Informacao');
        $response->assertSee('e-SIC');
    }

    public function test_portal_lai_store_cria_solicitacao(): void
    {
        $response = $this->post("/{$this->tenant->slug}/portal/lai", [
            'nome_solicitante' => 'Teste Portal',
            'email_solicitante' => 'portal@teste.com',
            'cpf_solicitante' => '999.888.777-66',
            'assunto' => 'Solicitacao via portal publico',
            'descricao' => 'Descricao da solicitacao via portal publico para teste automatizado.',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHas('success');

        $solicitacao = SolicitacaoLai::where('email_solicitante', 'portal@teste.com')->first();
        $this->assertNotNull($solicitacao);
        $this->assertStringStartsWith('LAI-', $solicitacao->protocolo);
        $this->assertEquals(StatusSolicitacaoLai::Recebida, $solicitacao->status);
    }

    public function test_portal_lai_store_validacao_campos_obrigatorios(): void
    {
        $response = $this->post("/{$this->tenant->slug}/portal/lai", []);
        $response->assertStatus(302);
        $response->assertSessionHasErrors(['nome_solicitante', 'email_solicitante', 'cpf_solicitante', 'assunto', 'descricao']);
    }

    public function test_portal_lai_consultar_acessivel(): void
    {
        $response = $this->get("/{$this->tenant->slug}/portal/lai/consultar");
        $response->assertStatus(200);
        $response->assertSee('Consultar Solicitacao');
    }

    public function test_portal_lai_show_exibe_por_protocolo(): void
    {
        $solicitacao = SolicitacaoLaiService::criar([
            'nome_solicitante' => 'Show Test',
            'email_solicitante' => 'show@teste.com',
            'cpf_solicitante' => '111.999.111-99',
            'assunto' => 'Teste show publico',
            'descricao' => 'Descricao de teste show publico para validacao',
        ]);

        $response = $this->get("/{$this->tenant->slug}/portal/lai/{$solicitacao->protocolo}?email=show@teste.com");
        $response->assertStatus(200);
        $response->assertSee($solicitacao->protocolo);
        $response->assertSee('Teste show publico');
    }

    public function test_portal_lai_show_protocolo_invalido_redireciona(): void
    {
        $response = $this->get("/{$this->tenant->slug}/portal/lai/LAI-2026-999999?email=nao@existe.com");
        $response->assertStatus(302);
        $response->assertSessionHas('error');
    }

    // ═══════════════════════════════════════════════════════════════
    // Controller Interno — SolicitacoesLaiController
    // ═══════════════════════════════════════════════════════════════

    public function test_interno_index_listagem_com_paginacao(): void
    {
        SolicitacaoLaiService::criar([
            'nome_solicitante' => 'Index Test',
            'email_solicitante' => 'index@teste.com',
            'cpf_solicitante' => '100.200.300-40',
            'assunto' => 'Teste listagem interna',
            'descricao' => 'Descricao de teste listagem interna solicitacoes',
        ]);

        $this->grantPermission($this->admin, 'lai.visualizar');

        $response = $this->actingAs($this->admin)
            ->withSession(['mfa_verified' => true])
            ->get(route('tenant.solicitacoes-lai.index'));

        $response->assertStatus(200);
        $response->assertSee('Solicitacoes de Acesso a Informacao');
        $response->assertSee('Index Test');
    }

    public function test_interno_show_detalhe_com_historico(): void
    {
        $solicitacao = SolicitacaoLaiService::criar([
            'nome_solicitante' => 'Show Interno',
            'email_solicitante' => 'showint@teste.com',
            'cpf_solicitante' => '500.600.700-80',
            'assunto' => 'Teste detalhe interno',
            'descricao' => 'Descricao de teste detalhe interno com historico',
        ]);

        $this->grantPermission($this->admin, 'lai.visualizar');

        $response = $this->actingAs($this->admin)
            ->withSession(['mfa_verified' => true])
            ->get(route('tenant.solicitacoes-lai.show', $solicitacao));

        $response->assertStatus(200);
        $response->assertSee($solicitacao->protocolo);
        $response->assertSee('Historico');
    }

    public function test_interno_analisar_muda_status(): void
    {
        $solicitacao = SolicitacaoLaiService::criar([
            'nome_solicitante' => 'Analisar Interno',
            'email_solicitante' => 'analisarint@teste.com',
            'cpf_solicitante' => '600.700.800-90',
            'assunto' => 'Teste analisar interno',
            'descricao' => 'Descricao de teste analisar status interno',
        ]);

        $this->grantPermission($this->admin, 'lai.analisar');

        $response = $this->actingAs($this->admin)
            ->withSession(['mfa_verified' => true])
            ->post(route('tenant.solicitacoes-lai.analisar', $solicitacao));

        $response->assertStatus(302);
        $response->assertSessionHas('success');

        $solicitacao->refresh();
        $this->assertEquals(StatusSolicitacaoLai::EmAnalise, $solicitacao->status);
    }

    public function test_interno_responder_registra_resposta(): void
    {
        $solicitacao = SolicitacaoLaiService::criar([
            'nome_solicitante' => 'Responder Interno',
            'email_solicitante' => 'respint@teste.com',
            'cpf_solicitante' => '700.800.900-01',
            'assunto' => 'Teste responder interno',
            'descricao' => 'Descricao de teste responder via controller interno',
        ]);

        $this->grantPermission($this->admin, 'lai.responder');

        $response = $this->actingAs($this->admin)
            ->withSession(['mfa_verified' => true])
            ->post(route('tenant.solicitacoes-lai.responder', $solicitacao), [
                'resposta' => 'Resposta detalhada com todas as informacoes solicitadas pelo cidadao.',
                'classificacao_resposta' => ClassificacaoRespostaLai::Deferida->value,
            ]);

        $response->assertStatus(302);
        $response->assertSessionHas('success');

        $solicitacao->refresh();
        $this->assertEquals(StatusSolicitacaoLai::Respondida, $solicitacao->status);
    }

    public function test_interno_prorrogar_registra_prorrogacao(): void
    {
        $solicitacao = SolicitacaoLaiService::criar([
            'nome_solicitante' => 'Prorrogar Interno',
            'email_solicitante' => 'prorrint@teste.com',
            'cpf_solicitante' => '800.900.000-12',
            'assunto' => 'Teste prorrogar interno',
            'descricao' => 'Descricao de teste prorrogar via controller interno',
        ]);

        $this->grantPermission($this->admin, 'lai.prorrogar');

        $response = $this->actingAs($this->admin)
            ->withSession(['mfa_verified' => true])
            ->post(route('tenant.solicitacoes-lai.prorrogar', $solicitacao), [
                'justificativa_prorrogacao' => 'Justificativa valida para prorrogacao de prazo conforme legislacao vigente.',
            ]);

        $response->assertStatus(302);
        $response->assertSessionHas('success');

        $solicitacao->refresh();
        $this->assertEquals(StatusSolicitacaoLai::Prorrogada, $solicitacao->status);
        $this->assertNotNull($solicitacao->prazo_estendido);
    }

    // ═══════════════════════════════════════════════════════════════
    // PermissionSeeder — 6 novas permissoes LAI
    // ═══════════════════════════════════════════════════════════════

    public function test_permissoes_lai_existem(): void
    {
        $permissoes = [
            'lai.visualizar',
            'lai.analisar',
            'lai.responder',
            'lai.prorrogar',
            'lai.indeferir',
            'lai.relatorio',
        ];

        foreach ($permissoes as $nome) {
            $exists = DB::connection('tenant')
                ->table('permissions')
                ->where('nome', $nome)
                ->where('grupo', 'lai')
                ->exists();
            $this->assertTrue($exists, "Permissao {$nome} nao encontrada");
        }
    }

    // ═══════════════════════════════════════════════════════════════
    // Helper
    // ═══════════════════════════════════════════════════════════════

    private function grantPermission(User $user, string $permissionName): void
    {
        $permissionId = DB::connection('tenant')
            ->table('permissions')
            ->where('nome', $permissionName)
            ->value('id');

        if ($permissionId) {
            DB::connection('tenant')->table('user_permissions')->updateOrInsert(
                ['user_id' => $user->id, 'permission_id' => $permissionId],
                ['created_at' => now()]
            );
        }
    }
}
