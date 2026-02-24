<?php

namespace Tests\Feature\Rbac;

use App\Models\Permission;
use App\Models\User;
use App\Services\PermissaoService;
use Tests\TestCase;
use Tests\Traits\RunsTenantMigrations;
use Tests\Traits\SeedsTenantData;

class PermissaoAuditoriaTest extends TestCase
{
    use RunsTenantMigrations;
    use SeedsTenantData;

    protected User $admin;
    protected User $fiscal;
    protected Permission $permissao;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpTenant();
        $this->seedBaseData();

        $this->admin = $this->createAdminUser();
        $this->fiscal = $this->createUserWithRole('fiscal_contrato');
        $this->permissao = Permission::where('nome', 'usuario.criar')->first();
    }

    public function test_atribuir_permissao_registra_auditoria(): void
    {
        PermissaoService::atribuirPermissaoIndividual(
            $this->fiscal,
            $this->permissao,
            null,
            $this->admin
        );

        $this->assertDatabaseHas('historico_alteracoes', [
            'auditable_type' => User::class,
            'auditable_id' => $this->fiscal->id,
            'campo_alterado' => 'permissao_concedida',
        ], 'tenant');
    }

    public function test_revogar_permissao_registra_auditoria(): void
    {
        PermissaoService::atribuirPermissaoIndividual(
            $this->fiscal,
            $this->permissao,
            null,
            $this->admin
        );

        PermissaoService::revogarPermissaoIndividual(
            $this->fiscal,
            $this->permissao,
            $this->admin
        );

        $this->assertDatabaseHas('historico_alteracoes', [
            'auditable_type' => User::class,
            'auditable_id' => $this->fiscal->id,
            'campo_alterado' => 'permissao_revogada',
            'valor_anterior' => $this->permissao->nome,
        ], 'tenant');
    }
}
