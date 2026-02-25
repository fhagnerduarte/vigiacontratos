<?php

namespace Tests\Feature\Controllers;

use App\Enums\TipoContrato;
use App\Enums\TipoDocumentoContratual;
use App\Models\ConfiguracaoChecklistDocumento;
use App\Models\Contrato;
use App\Models\Documento;
use App\Models\Role;
use App\Models\User;
use App\Services\DocumentoService;
use Tests\TestCase;
use Tests\Traits\RunsTenantMigrations;
use Tests\Traits\SeedsTenantData;

class ConfiguracaoChecklistDocumentoControllerTest extends TestCase
{
    use RunsTenantMigrations;
    use SeedsTenantData;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpTenant();
        $this->seedBaseData();
        DocumentoService::limparCacheChecklist();
        $this->admin = $this->createAdminUser([
            'mfa_secret' => 'TESTSECRETKEY123',
            'mfa_enabled_at' => now(),
            'mfa_recovery_codes' => encrypt(json_encode(['AAAA-BBBB'])),
        ]);
    }

    protected function tearDown(): void
    {
        DocumentoService::limparCacheChecklist();
        parent::tearDown();
    }

    protected function actAsAdmin(): self
    {
        return $this->actingAs($this->admin)->withSession(['mfa_verified' => true]);
    }

    // ─── INDEX ──────────────────────────────────────────────

    public function test_index_exibe_matriz_de_configuracao(): void
    {
        $response = $this->actAsAdmin()->get(
            route('tenant.configuracoes-checklist.index')
        );

        $response->assertStatus(200);
        $response->assertViewIs('tenant.configuracoes-checklist.index');
        $response->assertViewHas('tiposContrato');
        $response->assertViewHas('tiposDocumento');
        $response->assertViewHas('configuracoes');
    }

    public function test_index_requer_permissao_documento_configurar(): void
    {
        $roleSem = Role::factory()->create(['nome' => 'sem_perm_doc_config']);
        $user = User::factory()->create(['role_id' => $roleSem->id, 'is_ativo' => true]);

        $response = $this->actingAs($user)
            ->withSession(['mfa_verified' => true])
            ->get(route('tenant.configuracoes-checklist.index'));

        $this->assertTrue(
            in_array($response->getStatusCode(), [403, 404]),
            'Esperado 403 ou 404, recebido: ' . $response->getStatusCode()
        );
    }

    public function test_index_requer_autenticacao(): void
    {
        $response = $this->get(route('tenant.configuracoes-checklist.index'));

        $response->assertRedirect();
    }

    // ─── UPDATE ─────────────────────────────────────────────

    public function test_update_salva_configuracao_corretamente(): void
    {
        $response = $this->actAsAdmin()->put(
            route('tenant.configuracoes-checklist.update'),
            ['checklist' => [
                TipoContrato::Servico->value => [
                    TipoDocumentoContratual::ContratoOriginal->value => '1',
                    TipoDocumentoContratual::NotaEmpenho->value => '1',
                ],
            ]]
        );

        $response->assertRedirect(route('tenant.configuracoes-checklist.index'));
        $response->assertSessionHas('success');

        // Servico deve ter 2 ativos
        $ativos = ConfiguracaoChecklistDocumento::where('tipo_contrato', TipoContrato::Servico)
            ->where('is_ativo', true)
            ->count();
        $this->assertEquals(2, $ativos);
    }

    public function test_update_desmarca_documento_quando_ausente_do_payload(): void
    {
        // Configurar previamente como ativo
        ConfiguracaoChecklistDocumento::updateOrCreate(
            ['tipo_contrato' => TipoContrato::Obra, 'tipo_documento' => TipoDocumentoContratual::ContratoOriginal],
            ['is_ativo' => true]
        );

        // Enviar payload sem Obra.ContratoOriginal
        $response = $this->actAsAdmin()->put(
            route('tenant.configuracoes-checklist.update'),
            ['checklist' => []]
        );

        $response->assertRedirect();

        $config = ConfiguracaoChecklistDocumento::where('tipo_contrato', TipoContrato::Obra)
            ->where('tipo_documento', TipoDocumentoContratual::ContratoOriginal)
            ->first();

        $this->assertFalse($config->is_ativo, 'Ausente do payload deve ser marcado como inativo');
    }

    public function test_update_requer_permissao_documento_configurar(): void
    {
        $roleSem = Role::factory()->create(['nome' => 'sem_perm_doc_upd']);
        $user = User::factory()->create(['role_id' => $roleSem->id, 'is_ativo' => true]);

        $response = $this->actingAs($user)
            ->withSession(['mfa_verified' => true])
            ->put(route('tenant.configuracoes-checklist.update'), ['checklist' => []]);

        $this->assertTrue(
            in_array($response->getStatusCode(), [403, 404]),
            'Esperado 403 ou 404, recebido: ' . $response->getStatusCode()
        );
    }

    // ─── INTEGRACAO CHECKLIST ──────────────────────────────

    public function test_checklist_varia_por_tipo_contrato(): void
    {
        // Desativar tudo primeiro para ter controle total
        ConfiguracaoChecklistDocumento::query()->update(['is_ativo' => false]);

        // Servico: 2 docs obrigatorios
        ConfiguracaoChecklistDocumento::updateOrCreate(
            ['tipo_contrato' => TipoContrato::Servico, 'tipo_documento' => TipoDocumentoContratual::ContratoOriginal],
            ['is_ativo' => true]
        );
        ConfiguracaoChecklistDocumento::updateOrCreate(
            ['tipo_contrato' => TipoContrato::Servico, 'tipo_documento' => TipoDocumentoContratual::NotaEmpenho],
            ['is_ativo' => true]
        );

        // Obra: 3 docs obrigatorios
        ConfiguracaoChecklistDocumento::updateOrCreate(
            ['tipo_contrato' => TipoContrato::Obra, 'tipo_documento' => TipoDocumentoContratual::ContratoOriginal],
            ['is_ativo' => true]
        );
        ConfiguracaoChecklistDocumento::updateOrCreate(
            ['tipo_contrato' => TipoContrato::Obra, 'tipo_documento' => TipoDocumentoContratual::NotaEmpenho],
            ['is_ativo' => true]
        );
        ConfiguracaoChecklistDocumento::updateOrCreate(
            ['tipo_contrato' => TipoContrato::Obra, 'tipo_documento' => TipoDocumentoContratual::ParecerJuridico],
            ['is_ativo' => true]
        );

        DocumentoService::limparCacheChecklist();

        $contratoServico = Contrato::factory()->create(['tipo' => TipoContrato::Servico]);
        $contratoServico->load('documentos');
        $checklistServico = DocumentoService::verificarChecklist($contratoServico);

        $contratoObra = Contrato::factory()->create(['tipo' => TipoContrato::Obra]);
        $contratoObra->load('documentos');
        $checklistObra = DocumentoService::verificarChecklist($contratoObra);

        $this->assertCount(2, $checklistServico, 'Servico deve ter 2 itens no checklist');
        $this->assertCount(3, $checklistObra, 'Obra deve ter 3 itens no checklist');
    }

    public function test_cache_invalidado_apos_update(): void
    {
        // Desativar tudo e configurar apenas 1 doc
        ConfiguracaoChecklistDocumento::query()->update(['is_ativo' => false]);
        ConfiguracaoChecklistDocumento::updateOrCreate(
            ['tipo_contrato' => TipoContrato::Servico, 'tipo_documento' => TipoDocumentoContratual::ContratoOriginal],
            ['is_ativo' => true]
        );

        // Popular cache
        $checklist1 = DocumentoService::obterChecklistPorTipo(TipoContrato::Servico);
        $this->assertCount(1, $checklist1);

        // Atualizar via controller (adicionar mais 1)
        $this->actAsAdmin()->put(
            route('tenant.configuracoes-checklist.update'),
            ['checklist' => [
                TipoContrato::Servico->value => [
                    TipoDocumentoContratual::ContratoOriginal->value => '1',
                    TipoDocumentoContratual::NotaEmpenho->value => '1',
                ],
            ]]
        );

        // Cache deve ter sido limpo pelo controller
        $checklist2 = DocumentoService::obterChecklistPorTipo(TipoContrato::Servico);
        $this->assertCount(2, $checklist2, 'Apos update via controller, cache deve refletir nova config');
    }

    public function test_admin_role_tem_acesso(): void
    {
        $response = $this->actAsAdmin()->get(
            route('tenant.configuracoes-checklist.index')
        );

        $response->assertStatus(200);
    }

    public function test_fiscal_nao_tem_acesso(): void
    {
        $user = $this->createUserWithRole('fiscal_contrato');

        $response = $this->actingAs($user)
            ->withSession(['mfa_verified' => true])
            ->get(route('tenant.configuracoes-checklist.index'));

        $this->assertTrue(
            in_array($response->getStatusCode(), [403, 404]),
            'Fiscal nao deve ter acesso a configuracao de checklist'
        );
    }
}
