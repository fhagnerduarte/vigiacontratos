<?php

namespace Tests\Unit\Models;

use App\Models\Contrato;
use App\Models\HistoricoAlteracao;
use App\Models\User;
use Tests\TestCase;
use Tests\Traits\RunsTenantMigrations;
use Tests\Traits\SeedsTenantData;

class HistoricoAlteracaoTest extends TestCase
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
        $contrato = Contrato::factory()->create();
        $user = User::factory()->create();

        $historico = HistoricoAlteracao::create([
            'auditable_type' => Contrato::class,
            'auditable_id' => $contrato->id,
            'campo_alterado' => 'objeto',
            'valor_anterior' => 'old',
            'valor_novo' => 'new',
            'user_id' => $user->id,
            'role_nome' => 'admin',
            'ip_address' => '127.0.0.1',
        ]);

        $this->assertDatabaseHas('historico_alteracoes', [
            'id' => $historico->id,
            'campo_alterado' => 'objeto',
        ], 'tenant');
    }

    public function test_update_lanca_runtime_exception(): void
    {
        $contrato = Contrato::factory()->create();
        $user = User::factory()->create();

        $historico = HistoricoAlteracao::create([
            'auditable_type' => Contrato::class,
            'auditable_id' => $contrato->id,
            'campo_alterado' => 'objeto',
            'valor_anterior' => 'old',
            'valor_novo' => 'new',
            'user_id' => $user->id,
            'role_nome' => 'admin',
            'ip_address' => '127.0.0.1',
        ]);

        $this->expectException(\RuntimeException::class);

        $historico->update(['valor_novo' => 'alterado']);
    }

    public function test_delete_lanca_runtime_exception(): void
    {
        $contrato = Contrato::factory()->create();
        $user = User::factory()->create();

        $historico = HistoricoAlteracao::create([
            'auditable_type' => Contrato::class,
            'auditable_id' => $contrato->id,
            'campo_alterado' => 'objeto',
            'valor_anterior' => 'old',
            'valor_novo' => 'new',
            'user_id' => $user->id,
            'role_nome' => 'admin',
            'ip_address' => '127.0.0.1',
        ]);

        $this->expectException(\RuntimeException::class);

        $historico->delete();
    }
}
