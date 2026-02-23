<?php

namespace Tests\Unit\Models;

use App\Enums\StatusIntegridade;
use App\Models\Documento;
use App\Models\LogIntegridadeDocumento;
use Tests\TestCase;
use Tests\Traits\RunsTenantMigrations;
use Tests\Traits\SeedsTenantData;

class LogIntegridadeDocumentoTest extends TestCase
{
    use RunsTenantMigrations;
    use SeedsTenantData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpTenant();
        $this->seedBaseData();
    }

    public function test_criar_log_integridade_com_status_ok(): void
    {
        $documento = Documento::factory()->create();

        $log = LogIntegridadeDocumento::create([
            'documento_id' => $documento->id,
            'hash_esperado' => hash('sha256', 'conteudo-original'),
            'hash_calculado' => hash('sha256', 'conteudo-original'),
            'status' => StatusIntegridade::Ok,
            'detectado_em' => now(),
        ]);

        $this->assertDatabaseHas('log_integridade_documentos', [
            'id' => $log->id,
            'status' => 'ok',
        ], 'tenant');

        $this->assertEquals(StatusIntegridade::Ok, $log->status);
    }

    public function test_criar_log_integridade_com_status_divergente(): void
    {
        $documento = Documento::factory()->create();

        $log = LogIntegridadeDocumento::create([
            'documento_id' => $documento->id,
            'hash_esperado' => hash('sha256', 'conteudo-original'),
            'hash_calculado' => hash('sha256', 'conteudo-alterado'),
            'status' => StatusIntegridade::Divergente,
            'detectado_em' => now(),
        ]);

        $this->assertEquals(StatusIntegridade::Divergente, $log->status);
    }

    public function test_criar_log_integridade_com_status_arquivo_ausente(): void
    {
        $documento = Documento::factory()->create();

        $log = LogIntegridadeDocumento::create([
            'documento_id' => $documento->id,
            'hash_esperado' => hash('sha256', 'conteudo'),
            'hash_calculado' => null,
            'status' => StatusIntegridade::ArquivoAusente,
            'detectado_em' => now(),
        ]);

        $this->assertEquals(StatusIntegridade::ArquivoAusente, $log->status);
    }

    public function test_imutavel_nao_permite_update(): void
    {
        $documento = Documento::factory()->create();

        $log = LogIntegridadeDocumento::create([
            'documento_id' => $documento->id,
            'hash_esperado' => hash('sha256', 'conteudo'),
            'hash_calculado' => hash('sha256', 'conteudo'),
            'status' => StatusIntegridade::Ok,
            'detectado_em' => now(),
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('imutáveis');

        $log->update(['status' => StatusIntegridade::Divergente->value]);
    }

    public function test_imutavel_nao_permite_delete(): void
    {
        $documento = Documento::factory()->create();

        $log = LogIntegridadeDocumento::create([
            'documento_id' => $documento->id,
            'hash_esperado' => hash('sha256', 'conteudo'),
            'hash_calculado' => hash('sha256', 'conteudo'),
            'status' => StatusIntegridade::Ok,
            'detectado_em' => now(),
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('não podem ser excluídos');

        $log->delete();
    }

    public function test_pertence_a_documento(): void
    {
        $documento = Documento::factory()->create();

        $log = LogIntegridadeDocumento::create([
            'documento_id' => $documento->id,
            'hash_esperado' => hash('sha256', 'conteudo'),
            'hash_calculado' => hash('sha256', 'conteudo'),
            'status' => StatusIntegridade::Ok,
            'detectado_em' => now(),
        ]);

        $this->assertInstanceOf(Documento::class, $log->documento);
        $this->assertEquals($documento->id, $log->documento->id);
    }

    public function test_casts_datetime_detectado_em(): void
    {
        $documento = Documento::factory()->create();

        $log = LogIntegridadeDocumento::create([
            'documento_id' => $documento->id,
            'hash_esperado' => hash('sha256', 'conteudo'),
            'hash_calculado' => hash('sha256', 'conteudo'),
            'status' => StatusIntegridade::Ok,
            'detectado_em' => '2026-02-23 10:30:00',
        ]);

        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $log->detectado_em);
    }

    public function test_created_at_preenchido_automaticamente(): void
    {
        $documento = Documento::factory()->create();

        $log = LogIntegridadeDocumento::create([
            'documento_id' => $documento->id,
            'hash_esperado' => hash('sha256', 'conteudo'),
            'hash_calculado' => hash('sha256', 'conteudo'),
            'status' => StatusIntegridade::Ok,
            'detectado_em' => now(),
        ]);

        $this->assertNotNull($log->created_at);
    }
}
