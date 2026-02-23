<?php

namespace Tests\Unit\Models;

use App\Models\Documento;
use App\Models\LogAcessoDocumento;
use App\Models\User;
use Tests\TestCase;
use Tests\Traits\RunsTenantMigrations;
use Tests\Traits\SeedsTenantData;

class LogAcessoDocumentoTest extends TestCase
{
    use RunsTenantMigrations;
    use SeedsTenantData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpTenant();
        $this->seedBaseData();
    }

    public function test_create_funciona_normalmente(): void
    {
        $documento = Documento::factory()->create();
        $user = User::factory()->create();

        $log = LogAcessoDocumento::create([
            'documento_id' => $documento->id,
            'user_id' => $user->id,
            'acao' => 'download',
            'ip_address' => '127.0.0.1',
        ]);

        $this->assertDatabaseHas('log_acesso_documentos', [
            'id' => $log->id,
            'acao' => 'download',
        ], 'tenant');
    }

    public function test_update_lanca_runtime_exception(): void
    {
        $documento = Documento::factory()->create();
        $user = User::factory()->create();

        $log = LogAcessoDocumento::create([
            'documento_id' => $documento->id,
            'user_id' => $user->id,
            'acao' => 'download',
            'ip_address' => '127.0.0.1',
        ]);

        $this->expectException(\RuntimeException::class);

        $log->update(['ip_address' => '192.168.1.1']);
    }

    public function test_delete_lanca_runtime_exception(): void
    {
        $documento = Documento::factory()->create();
        $user = User::factory()->create();

        $log = LogAcessoDocumento::create([
            'documento_id' => $documento->id,
            'user_id' => $user->id,
            'acao' => 'download',
            'ip_address' => '127.0.0.1',
        ]);

        $this->expectException(\RuntimeException::class);

        $log->delete();
    }
}
