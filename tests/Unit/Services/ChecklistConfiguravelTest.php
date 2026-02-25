<?php

namespace Tests\Unit\Services;

use App\Enums\StatusCompletudeDocumental;
use App\Enums\TipoContrato;
use App\Enums\TipoDocumentoContratual;
use App\Models\ConfiguracaoChecklistDocumento;
use App\Models\Contrato;
use App\Models\Documento;
use App\Services\DocumentoService;
use Tests\TestCase;
use Tests\Traits\RunsTenantMigrations;
use Tests\Traits\SeedsTenantData;

class ChecklistConfiguravelTest extends TestCase
{
    use RunsTenantMigrations;
    use SeedsTenantData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpTenant();
        $this->seedBaseData();
        DocumentoService::limparCacheChecklist();
    }

    protected function tearDown(): void
    {
        DocumentoService::limparCacheChecklist();
        parent::tearDown();
    }

    private function upsertConfig(TipoContrato $tipoContrato, TipoDocumentoContratual $tipoDocumento, bool $isAtivo = true): ConfiguracaoChecklistDocumento
    {
        return ConfiguracaoChecklistDocumento::updateOrCreate(
            ['tipo_contrato' => $tipoContrato, 'tipo_documento' => $tipoDocumento],
            ['is_ativo' => $isAtivo]
        );
    }

    // ─── obterChecklistPorTipo ────────────────────────────────

    public function test_obterChecklistPorTipo_retorna_configurado_do_banco(): void
    {
        $this->upsertConfig(TipoContrato::Servico, TipoDocumentoContratual::ContratoOriginal);
        $this->upsertConfig(TipoContrato::Servico, TipoDocumentoContratual::NotaEmpenho);

        $checklist = DocumentoService::obterChecklistPorTipo(TipoContrato::Servico);

        $this->assertCount(2, $checklist);
        $this->assertContains(TipoDocumentoContratual::ContratoOriginal, $checklist);
        $this->assertContains(TipoDocumentoContratual::NotaEmpenho, $checklist);
    }

    public function test_obterChecklistPorTipo_fallback_quando_tabela_vazia(): void
    {
        // Garantir que nao ha registros ativos para Locacao
        ConfiguracaoChecklistDocumento::where('tipo_contrato', TipoContrato::Locacao)->delete();

        $checklist = DocumentoService::obterChecklistPorTipo(TipoContrato::Locacao);

        $this->assertCount(4, $checklist);
        $this->assertEquals(DocumentoService::CHECKLIST_OBRIGATORIO, $checklist);
    }

    public function test_obterChecklistPorTipo_ignora_inativos(): void
    {
        $this->upsertConfig(TipoContrato::Obra, TipoDocumentoContratual::ContratoOriginal, true);
        $this->upsertConfig(TipoContrato::Obra, TipoDocumentoContratual::NotaEmpenho, false);
        // Desativar qualquer outro que possa existir para Obra
        ConfiguracaoChecklistDocumento::where('tipo_contrato', TipoContrato::Obra)
            ->whereNotIn('tipo_documento', [
                TipoDocumentoContratual::ContratoOriginal,
                TipoDocumentoContratual::NotaEmpenho,
            ])
            ->update(['is_ativo' => false]);

        $checklist = DocumentoService::obterChecklistPorTipo(TipoContrato::Obra);

        $this->assertCount(1, $checklist);
        $this->assertContains(TipoDocumentoContratual::ContratoOriginal, $checklist);
    }

    public function test_obterChecklistPorTipo_usa_cache_estatico(): void
    {
        $this->upsertConfig(TipoContrato::Compra, TipoDocumentoContratual::ContratoOriginal);
        // Desativar outros para Compra
        ConfiguracaoChecklistDocumento::where('tipo_contrato', TipoContrato::Compra)
            ->where('tipo_documento', '!=', TipoDocumentoContratual::ContratoOriginal)
            ->update(['is_ativo' => false]);

        $checklist1 = DocumentoService::obterChecklistPorTipo(TipoContrato::Compra);
        $this->assertCount(1, $checklist1);

        // Adicionar novo registro — cache deve manter o valor antigo
        $this->upsertConfig(TipoContrato::Compra, TipoDocumentoContratual::NotaEmpenho);

        $checklist2 = DocumentoService::obterChecklistPorTipo(TipoContrato::Compra);
        $this->assertCount(1, $checklist2, 'Cache estatico deve manter valor anterior');
    }

    public function test_limparCacheChecklist_reseta_cache(): void
    {
        $this->upsertConfig(TipoContrato::Locacao, TipoDocumentoContratual::TermoReferencia);
        // Desativar outros para Locacao
        ConfiguracaoChecklistDocumento::where('tipo_contrato', TipoContrato::Locacao)
            ->where('tipo_documento', '!=', TipoDocumentoContratual::TermoReferencia)
            ->update(['is_ativo' => false]);

        $checklist1 = DocumentoService::obterChecklistPorTipo(TipoContrato::Locacao);
        $this->assertCount(1, $checklist1);

        $this->upsertConfig(TipoContrato::Locacao, TipoDocumentoContratual::Justificativa);

        DocumentoService::limparCacheChecklist();

        $checklist2 = DocumentoService::obterChecklistPorTipo(TipoContrato::Locacao);
        $this->assertCount(2, $checklist2, 'Apos limpar cache, deve re-consultar banco');
    }

    public function test_diferentes_tipos_nao_compartilham_cache(): void
    {
        // Configurar Servico com 1 doc e Obra com 2 docs
        ConfiguracaoChecklistDocumento::where('tipo_contrato', TipoContrato::Servico)->update(['is_ativo' => false]);
        ConfiguracaoChecklistDocumento::where('tipo_contrato', TipoContrato::Obra)->update(['is_ativo' => false]);

        $this->upsertConfig(TipoContrato::Servico, TipoDocumentoContratual::ContratoOriginal);
        $this->upsertConfig(TipoContrato::Obra, TipoDocumentoContratual::ContratoOriginal);
        $this->upsertConfig(TipoContrato::Obra, TipoDocumentoContratual::NotaEmpenho);

        $checklistServico = DocumentoService::obterChecklistPorTipo(TipoContrato::Servico);
        $checklistObra = DocumentoService::obterChecklistPorTipo(TipoContrato::Obra);

        $this->assertCount(1, $checklistServico);
        $this->assertCount(2, $checklistObra);
    }

    // ─── verificarChecklist ──────────────────────────────────

    public function test_verificarChecklist_usa_configuracao_por_tipo(): void
    {
        // Configurar apenas 2 documentos obrigatorios para Servico
        ConfiguracaoChecklistDocumento::where('tipo_contrato', TipoContrato::Servico)->update(['is_ativo' => false]);
        $this->upsertConfig(TipoContrato::Servico, TipoDocumentoContratual::ContratoOriginal);
        $this->upsertConfig(TipoContrato::Servico, TipoDocumentoContratual::NotaEmpenho);

        $contrato = Contrato::factory()->create(['tipo' => TipoContrato::Servico]);
        $contrato->load('documentos');

        $checklist = DocumentoService::verificarChecklist($contrato);

        $this->assertCount(2, $checklist);
        $this->assertEquals('Contrato Original', $checklist[0]['label']);
        $this->assertEquals('Nota de Empenho', $checklist[1]['label']);
    }

    // ─── statusCompletude via Model ──────────────────────────

    public function test_statusCompletude_usa_checklist_configuravel(): void
    {
        // Configurar apenas 1 documento obrigatorio para Compra
        ConfiguracaoChecklistDocumento::where('tipo_contrato', TipoContrato::Compra)->update(['is_ativo' => false]);
        $this->upsertConfig(TipoContrato::Compra, TipoDocumentoContratual::ContratoOriginal);

        $contrato = Contrato::factory()->create(['tipo' => TipoContrato::Compra]);

        Documento::factory()->create([
            'documentable_type' => Contrato::class,
            'documentable_id' => $contrato->id,
            'tipo_documento' => TipoDocumentoContratual::ContratoOriginal,
            'is_versao_atual' => true,
        ]);

        $contrato->load('documentos');

        $this->assertEquals(
            StatusCompletudeDocumental::Completo,
            $contrato->status_completude,
            'Com apenas 1 doc obrigatorio configurado e presente, completude deve ser Completo'
        );
    }

    public function test_statusCompletude_incompleto_quando_falta_doc_configurado(): void
    {
        // Configurar 2 documentos obrigatorios para Obra
        ConfiguracaoChecklistDocumento::where('tipo_contrato', TipoContrato::Obra)->update(['is_ativo' => false]);
        $this->upsertConfig(TipoContrato::Obra, TipoDocumentoContratual::ContratoOriginal);
        $this->upsertConfig(TipoContrato::Obra, TipoDocumentoContratual::ParecerJuridico);

        $contrato = Contrato::factory()->create(['tipo' => TipoContrato::Obra]);

        Documento::factory()->create([
            'documentable_type' => Contrato::class,
            'documentable_id' => $contrato->id,
            'tipo_documento' => TipoDocumentoContratual::ContratoOriginal,
            'is_versao_atual' => true,
        ]);

        $contrato->load('documentos');

        $this->assertEquals(
            StatusCompletudeDocumental::Parcial,
            $contrato->status_completude,
            'Falta ParecerJuridico, mas tem ContratoOriginal — deve ser Parcial'
        );
    }
}
