<?php

namespace Tests\Unit\Services;

use App\Models\Contrato;
use App\Models\HistoricoAlteracao;
use App\Services\AuditoriaService;
use Tests\TestCase;
use Tests\Traits\RunsTenantMigrations;
use Tests\Traits\SeedsTenantData;

class AuditoriaServiceTest extends TestCase
{
    use RunsTenantMigrations;
    use SeedsTenantData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpTenant();
        $this->seedBaseData();
    }

    public function test_registrar_cria_historico(): void
    {
        $user = $this->createAdminUser();
        $contrato = Contrato::factory()->vigente()->create();

        AuditoriaService::registrar($contrato, 'objeto', 'Antigo', 'Novo', $user, '127.0.0.1');

        $this->assertDatabaseHas('historico_alteracoes', [
            'auditable_type' => $contrato->getMorphClass(),
            'auditable_id' => $contrato->id,
            'campo_alterado' => 'objeto',
            'valor_anterior' => 'Antigo',
            'valor_novo' => 'Novo',
            'user_id' => $user->id,
        ], 'tenant');
    }

    public function test_registrar_snapshot_role_nome(): void
    {
        $user = $this->createAdminUser();
        $contrato = Contrato::factory()->vigente()->create();

        AuditoriaService::registrar($contrato, 'campo', null, 'valor', $user, '127.0.0.1');

        $historico = HistoricoAlteracao::where('user_id', $user->id)->first();

        $this->assertEquals('administrador_geral', $historico->role_nome);
    }

    public function test_registrar_criacao_campos_nao_nulos(): void
    {
        $user = $this->createAdminUser();
        $contrato = Contrato::factory()->vigente()->create();

        AuditoriaService::registrarCriacao($contrato, [
            'objeto' => 'Contrato de teste',
            'valor_global' => 100000,
            'observacoes' => null, // null nao deve ser registrado
        ], $user, '127.0.0.1');

        $registros = HistoricoAlteracao::where('auditable_id', $contrato->id)
            ->where('auditable_type', $contrato->getMorphClass())
            ->get();

        // objeto e valor_global registrados, observacoes null nao
        $campos = $registros->pluck('campo_alterado')->toArray();
        $this->assertContains('objeto', $campos);
        $this->assertContains('valor_global', $campos);
        $this->assertNotContains('observacoes', $campos);
    }

    public function test_registrar_criacao_ignora_id_e_timestamps(): void
    {
        $user = $this->createAdminUser();
        $contrato = Contrato::factory()->vigente()->create();

        AuditoriaService::registrarCriacao($contrato, [
            'id' => 1,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
            'objeto' => 'Contrato real',
        ], $user, '127.0.0.1');

        $registros = HistoricoAlteracao::where('auditable_id', $contrato->id)
            ->where('auditable_type', $contrato->getMorphClass())
            ->get();

        $campos = $registros->pluck('campo_alterado')->toArray();
        $this->assertNotContains('id', $campos);
        $this->assertNotContains('created_at', $campos);
        $this->assertNotContains('updated_at', $campos);
    }

    public function test_registrar_alteracoes_detecta_mudancas(): void
    {
        $user = $this->createAdminUser();
        $contrato = Contrato::factory()->vigente()->create();

        $originais = ['objeto' => 'Antigo', 'valor_global' => '100000'];
        $novos = ['objeto' => 'Novo', 'valor_global' => '100000']; // valor_global nao mudou

        AuditoriaService::registrarAlteracoes($contrato, $originais, $novos, $user, '127.0.0.1');

        $registros = HistoricoAlteracao::where('auditable_id', $contrato->id)
            ->where('auditable_type', $contrato->getMorphClass())
            ->get();

        $this->assertCount(1, $registros);
        $this->assertEquals('objeto', $registros->first()->campo_alterado);
    }

    public function test_registrar_alteracoes_ignora_timestamps(): void
    {
        $user = $this->createAdminUser();
        $contrato = Contrato::factory()->vigente()->create();

        $originais = ['updated_at' => '2026-01-01'];
        $novos = ['updated_at' => '2026-02-01'];

        AuditoriaService::registrarAlteracoes($contrato, $originais, $novos, $user, '127.0.0.1');

        $count = HistoricoAlteracao::where('auditable_id', $contrato->id)
            ->where('auditable_type', $contrato->getMorphClass())
            ->count();

        $this->assertEquals(0, $count);
    }
}
