<?php

namespace Tests\Unit\Models;

use App\Models\Alerta;
use App\Models\LogNotificacao;
use Tests\TestCase;
use Tests\Traits\RunsTenantMigrations;
use Tests\Traits\SeedsTenantData;

class LogNotificacaoTest extends TestCase
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
        $alerta = Alerta::factory()->create();

        $log = LogNotificacao::create([
            'alerta_id' => $alerta->id,
            'canal' => 'email',
            'destinatario' => 'test@test.com',
            'data_envio' => now(),
            'sucesso' => true,
            'tentativa_numero' => 1,
            'resposta_gateway' => null,
        ]);

        $this->assertDatabaseHas('log_notificacoes', [
            'id' => $log->id,
            'destinatario' => 'test@test.com',
        ], 'tenant');
    }

    public function test_update_lanca_runtime_exception(): void
    {
        $alerta = Alerta::factory()->create();

        $log = LogNotificacao::create([
            'alerta_id' => $alerta->id,
            'canal' => 'email',
            'destinatario' => 'test@test.com',
            'data_envio' => now(),
            'sucesso' => true,
            'tentativa_numero' => 1,
            'resposta_gateway' => null,
        ]);

        $this->expectException(\RuntimeException::class);

        $log->update(['destinatario' => 'outro@test.com']);
    }

    public function test_delete_lanca_runtime_exception(): void
    {
        $alerta = Alerta::factory()->create();

        $log = LogNotificacao::create([
            'alerta_id' => $alerta->id,
            'canal' => 'email',
            'destinatario' => 'test@test.com',
            'data_envio' => now(),
            'sucesso' => true,
            'tentativa_numero' => 1,
            'resposta_gateway' => null,
        ]);

        $this->expectException(\RuntimeException::class);

        $log->delete();
    }
}
