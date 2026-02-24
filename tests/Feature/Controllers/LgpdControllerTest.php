<?php

namespace Tests\Feature\Controllers;

use App\Enums\TipoSolicitacaoLGPD;
use App\Models\Fornecedor;
use App\Models\LogLgpdSolicitacao;
use App\Models\User;
use Tests\TestCase;
use Tests\Traits\RunsTenantMigrations;
use Tests\Traits\SeedsTenantData;

class LgpdControllerTest extends TestCase
{
    use RunsTenantMigrations;
    use SeedsTenantData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpTenant();
        $this->seedBaseData();
    }

    private function actingAsAdminWithMfa(): User
    {
        $user = $this->createAdminUser([
            'mfa_secret' => 'TESTSECRETKEY123',
            'mfa_enabled_at' => now(),
            'mfa_recovery_codes' => encrypt(json_encode(['AAAA-BBBB'])),
        ]);
        $this->actingAs($user)->withSession(['mfa_verified' => true]);

        return $user;
    }

    public function test_index_exibe_listagem_de_solicitacoes(): void
    {
        $this->actingAsAdminWithMfa();

        $response = $this->get(route('tenant.lgpd.index'));

        $response->assertStatus(200);
        $response->assertViewIs('tenant.lgpd.index');
    }

    public function test_index_requer_autenticacao(): void
    {
        $response = $this->get(route('tenant.lgpd.index'));

        $response->assertRedirect(route('tenant.login'));
    }

    public function test_index_usuario_sem_permissao_retorna_403(): void
    {
        $user = $this->createUserWithRole('fiscal_contrato');

        $response = $this->actingAs($user)
            ->get(route('tenant.lgpd.index'));

        $response->assertStatus(403);
    }

    public function test_create_exibe_formulario(): void
    {
        $this->actingAsAdminWithMfa();

        $response = $this->get(route('tenant.lgpd.create'));

        $response->assertStatus(200);
        $response->assertViewIs('tenant.lgpd.create');
        $response->assertViewHas('tipos');
        $response->assertViewHas('fornecedores');
    }

    public function test_store_processa_anonimizacao_fornecedor(): void
    {
        $this->actingAsAdminWithMfa();
        $fornecedor = Fornecedor::factory()->create();

        $response = $this->post(route('tenant.lgpd.store'), [
            'tipo_solicitacao' => TipoSolicitacaoLGPD::Anonimizacao->value,
            'entidade_tipo' => 'fornecedor',
            'entidade_id' => $fornecedor->id,
            'justificativa' => 'Solicitacao formal do titular dos dados para anonimizacao.',
        ]);

        $response->assertRedirect(route('tenant.lgpd.index'));
        $response->assertSessionHas('success');

        // Verificar que fornecedor foi anonimizado
        $fresh = $fornecedor->fresh();
        $this->assertStringStartsWith('ANONIMIZADO_', $fresh->razao_social);
    }

    public function test_store_registra_solicitacao_pendente_para_tipos_nao_anonimizacao(): void
    {
        $this->actingAsAdminWithMfa();
        $fornecedor = Fornecedor::factory()->create();

        $response = $this->post(route('tenant.lgpd.store'), [
            'tipo_solicitacao' => TipoSolicitacaoLGPD::Portabilidade->value,
            'entidade_tipo' => 'fornecedor',
            'entidade_id' => $fornecedor->id,
            'justificativa' => 'Solicitacao de portabilidade dos dados conforme LGPD.',
        ]);

        $response->assertRedirect(route('tenant.lgpd.index'));

        $log = LogLgpdSolicitacao::latest('id')->first();
        $this->assertNotNull($log);
        $this->assertEquals('pendente', $log->status);
        $this->assertEquals(TipoSolicitacaoLGPD::Portabilidade, $log->tipo_solicitacao);
    }

    public function test_store_valida_campos_obrigatorios(): void
    {
        $this->actingAsAdminWithMfa();

        $response = $this->post(route('tenant.lgpd.store'), []);

        $response->assertSessionHasErrors([
            'tipo_solicitacao',
            'entidade_tipo',
            'entidade_id',
            'justificativa',
        ]);
    }

    public function test_store_rejeita_anonimizacao_de_usuario_ativo(): void
    {
        $this->actingAsAdminWithMfa();
        $userAlvo = $this->createUserWithRole('fiscal_contrato', ['is_ativo' => true]);

        $response = $this->post(route('tenant.lgpd.store'), [
            'tipo_solicitacao' => TipoSolicitacaoLGPD::Anonimizacao->value,
            'entidade_tipo' => 'usuario',
            'entidade_id' => $userAlvo->id,
            'justificativa' => 'Solicitacao formal para anonimizacao do usuario.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    public function test_show_exibe_detalhes_da_solicitacao(): void
    {
        $this->actingAsAdminWithMfa();
        $fornecedor = Fornecedor::factory()->create();

        // Criar solicitacao via anonimizacao
        $this->post(route('tenant.lgpd.store'), [
            'tipo_solicitacao' => TipoSolicitacaoLGPD::Anonimizacao->value,
            'entidade_tipo' => 'fornecedor',
            'entidade_id' => $fornecedor->id,
            'justificativa' => 'Solicitacao para visualizacao no show.',
        ]);

        $solicitacao = LogLgpdSolicitacao::latest('id')->first();

        $response = $this->get(route('tenant.lgpd.show', $solicitacao));

        $response->assertStatus(200);
        $response->assertViewIs('tenant.lgpd.show');
    }

    public function test_store_requer_permissao_lgpd_processar(): void
    {
        $user = $this->createUserWithRole('controladoria');
        $this->actingAs($user)->withSession(['mfa_verified' => true]);
        $fornecedor = Fornecedor::factory()->create();

        $response = $this->post(route('tenant.lgpd.store'), [
            'tipo_solicitacao' => TipoSolicitacaoLGPD::Anonimizacao->value,
            'entidade_tipo' => 'fornecedor',
            'entidade_id' => $fornecedor->id,
            'justificativa' => 'Tentativa sem permissao de processamento.',
        ]);

        // Controladoria tem lgpd.visualizar mas NAO lgpd.processar
        $response->assertStatus(403);
    }

    public function test_fornecedor_ja_anonimizado_retorna_erro(): void
    {
        $this->actingAsAdminWithMfa();
        $fornecedor = Fornecedor::factory()->create();

        // Primeira anonimizacao
        $this->post(route('tenant.lgpd.store'), [
            'tipo_solicitacao' => TipoSolicitacaoLGPD::Anonimizacao->value,
            'entidade_tipo' => 'fornecedor',
            'entidade_id' => $fornecedor->id,
            'justificativa' => 'Primeira anonimizacao do fornecedor.',
        ]);

        // Segunda tentativa deve falhar
        $response = $this->post(route('tenant.lgpd.store'), [
            'tipo_solicitacao' => TipoSolicitacaoLGPD::Anonimizacao->value,
            'entidade_tipo' => 'fornecedor',
            'entidade_id' => $fornecedor->id,
            'justificativa' => 'Tentativa duplicada de anonimizacao.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }
}
