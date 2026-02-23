<?php

namespace Tests\Unit\Models;

use App\Models\Aditivo;
use App\Models\Role;
use App\Models\WorkflowAprovacao;
use Tests\TestCase;
use Tests\Traits\RunsTenantMigrations;
use Tests\Traits\SeedsTenantData;

class WorkflowAprovacaoTest extends TestCase
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
        $aditivo = Aditivo::factory()->create();
        $role = Role::factory()->create();

        $workflow = WorkflowAprovacao::create([
            'aprovavel_type' => Aditivo::class,
            'aprovavel_id' => $aditivo->id,
            'etapa' => 'solicitacao',
            'etapa_ordem' => 1,
            'role_responsavel_id' => $role->id,
            'user_id' => null,
            'status' => 'pendente',
            'parecer' => null,
            'decided_at' => null,
        ]);

        $this->assertDatabaseHas('workflow_aprovacoes', [
            'id' => $workflow->id,
            'etapa' => 'solicitacao',
            'status' => 'pendente',
        ], 'tenant');
    }

    public function test_update_permitido_quando_status_pendente(): void
    {
        $aditivo = Aditivo::factory()->create();
        $role = Role::factory()->create();

        $workflow = WorkflowAprovacao::create([
            'aprovavel_type' => Aditivo::class,
            'aprovavel_id' => $aditivo->id,
            'etapa' => 'solicitacao',
            'etapa_ordem' => 1,
            'role_responsavel_id' => $role->id,
            'user_id' => null,
            'status' => 'pendente',
            'parecer' => null,
            'decided_at' => null,
        ]);

        $workflow->update([
            'status' => 'aprovado',
            'parecer' => 'Aprovado pelo gestor',
            'decided_at' => now(),
        ]);

        $this->assertDatabaseHas('workflow_aprovacoes', [
            'id' => $workflow->id,
            'status' => 'aprovado',
            'parecer' => 'Aprovado pelo gestor',
        ], 'tenant');
    }

    public function test_update_bloqueado_quando_status_aprovado(): void
    {
        $aditivo = Aditivo::factory()->create();
        $role = Role::factory()->create();

        $workflow = WorkflowAprovacao::create([
            'aprovavel_type' => Aditivo::class,
            'aprovavel_id' => $aditivo->id,
            'etapa' => 'solicitacao',
            'etapa_ordem' => 1,
            'role_responsavel_id' => $role->id,
            'user_id' => null,
            'status' => 'pendente',
            'parecer' => null,
            'decided_at' => null,
        ]);

        // Primeiro atualiza para aprovado (permitido pois status original e pendente)
        $workflow->update([
            'status' => 'aprovado',
            'parecer' => 'Aprovado',
            'decided_at' => now(),
        ]);

        // Recarrega para que getOriginal reflita o status salvo no banco
        $workflow->refresh();

        $this->expectException(\RuntimeException::class);

        $workflow->update(['parecer' => 'Tentativa de alteracao']);
    }

    public function test_update_bloqueado_quando_status_reprovado(): void
    {
        $aditivo = Aditivo::factory()->create();
        $role = Role::factory()->create();

        $workflow = WorkflowAprovacao::create([
            'aprovavel_type' => Aditivo::class,
            'aprovavel_id' => $aditivo->id,
            'etapa' => 'solicitacao',
            'etapa_ordem' => 1,
            'role_responsavel_id' => $role->id,
            'user_id' => null,
            'status' => 'pendente',
            'parecer' => null,
            'decided_at' => null,
        ]);

        // Primeiro atualiza para reprovado (permitido pois status original e pendente)
        $workflow->update([
            'status' => 'reprovado',
            'parecer' => 'Reprovado por falta de documentacao',
            'decided_at' => now(),
        ]);

        // Recarrega para que getOriginal reflita o status salvo no banco
        $workflow->refresh();

        $this->expectException(\RuntimeException::class);

        $workflow->update(['parecer' => 'Tentativa de alteracao']);
    }

    public function test_delete_sempre_bloqueado(): void
    {
        $aditivo = Aditivo::factory()->create();
        $role = Role::factory()->create();

        $workflow = WorkflowAprovacao::create([
            'aprovavel_type' => Aditivo::class,
            'aprovavel_id' => $aditivo->id,
            'etapa' => 'solicitacao',
            'etapa_ordem' => 1,
            'role_responsavel_id' => $role->id,
            'user_id' => null,
            'status' => 'pendente',
            'parecer' => null,
            'decided_at' => null,
        ]);

        $this->expectException(\RuntimeException::class);

        $workflow->delete();
    }
}
