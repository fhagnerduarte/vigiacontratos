<?php

namespace Tests\Feature\Relatorios;

use App\Models\Contrato;
use App\Models\Documento;
use App\Models\Role;
use App\Models\User;
use App\Services\RelatorioService;
use Tests\TestCase;
use Tests\Traits\RunsTenantMigrations;
use Tests\Traits\SeedsTenantData;

class DocumentosRelatorioTest extends TestCase
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

    // ─── RELATORIO DOCUMENTOS CONTRATO ──────────────────────

    public function test_relatorio_documentos_contrato_retorna_pdf(): void
    {
        $contrato = Contrato::factory()->create();
        Documento::factory()->create([
            'documentable_type' => Contrato::class,
            'documentable_id' => $contrato->id,
            'uploaded_by' => $this->admin->id,
        ]);

        $response = $this->actAsAdmin()->get(
            route('tenant.relatorios.documentos-contrato', $contrato)
        );

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_relatorio_documentos_exige_permissao(): void
    {
        $contrato = Contrato::factory()->create();
        $role = Role::factory()->create(['nome' => 'role_sem_download']);
        $user = User::factory()->create(['role_id' => $role->id]);

        $response = $this->actingAs($user)->get(
            route('tenant.relatorios.documentos-contrato', $contrato)
        );

        // Middleware permission retorna 403; SecretariaScope pode retornar 404
        $this->assertTrue(
            in_array($response->getStatusCode(), [403, 404]),
            'Esperado 403 ou 404, recebido: ' . $response->getStatusCode()
        );
    }

    public function test_relatorio_documentos_contrato_sem_documentos_funciona(): void
    {
        $contrato = Contrato::factory()->create();

        $response = $this->actAsAdmin()->get(
            route('tenant.relatorios.documentos-contrato', $contrato)
        );

        $response->assertStatus(200);
    }

    // ─── RELATORIO TCE SERVICE ──────────────────────────

    public function test_gerar_relatorio_tce_contrato_retorna_dados_completos(): void
    {
        $contrato = Contrato::factory()->create();
        Documento::factory()->count(3)->create([
            'documentable_type' => Contrato::class,
            'documentable_id' => $contrato->id,
            'uploaded_by' => $this->admin->id,
        ]);

        $dados = RelatorioService::gerarRelatorioTCEContrato($contrato);

        $this->assertArrayHasKey('municipio', $dados);
        $this->assertArrayHasKey('data_geracao', $dados);
        $this->assertArrayHasKey('contrato', $dados);
        $this->assertArrayHasKey('completude', $dados);
        $this->assertArrayHasKey('documentos', $dados);
        $this->assertArrayHasKey('total_documentos', $dados);

        // Dados do contrato
        $this->assertArrayHasKey('numero', $dados['contrato']);
        $this->assertArrayHasKey('objeto', $dados['contrato']);
        $this->assertArrayHasKey('fornecedor', $dados['contrato']);
        $this->assertArrayHasKey('cnpj', $dados['contrato']);
        $this->assertArrayHasKey('secretaria', $dados['contrato']);
        $this->assertArrayHasKey('valor_global', $dados['contrato']);
        $this->assertArrayHasKey('status', $dados['contrato']);

        $this->assertCount(3, $dados['documentos']);
        $this->assertEquals(3, $dados['total_documentos']);
    }

    public function test_gerar_relatorio_tce_contrato_alias_equivale_dados_documentos(): void
    {
        $contrato = Contrato::factory()->create();
        Documento::factory()->create([
            'documentable_type' => Contrato::class,
            'documentable_id' => $contrato->id,
            'uploaded_by' => $this->admin->id,
        ]);

        $dadosTCE = RelatorioService::gerarRelatorioTCEContrato($contrato);
        // Recarregar contrato para garantir estado limpo
        $contrato->refresh();
        $dadosDocs = RelatorioService::dadosDocumentosContrato($contrato);

        $this->assertEquals($dadosTCE['contrato'], $dadosDocs['contrato']);
        $this->assertEquals($dadosTCE['total_documentos'], $dadosDocs['total_documentos']);
    }

    public function test_gerar_relatorio_tce_contrato_campos_documento_corretos(): void
    {
        $contrato = Contrato::factory()->create();
        Documento::factory()->create([
            'documentable_type' => Contrato::class,
            'documentable_id' => $contrato->id,
            'uploaded_by' => $this->admin->id,
        ]);

        $dados = RelatorioService::gerarRelatorioTCEContrato($contrato);
        $doc = $dados['documentos'][0];

        // RN-133: campos obrigatorios do relatorio TCE
        $this->assertArrayHasKey('tipo_documento', $doc);
        $this->assertArrayHasKey('nome_arquivo', $doc);
        $this->assertArrayHasKey('versao', $doc);
        $this->assertArrayHasKey('data_upload', $doc);
        $this->assertArrayHasKey('responsavel', $doc);
        $this->assertArrayHasKey('tamanho_kb', $doc);
    }
}
