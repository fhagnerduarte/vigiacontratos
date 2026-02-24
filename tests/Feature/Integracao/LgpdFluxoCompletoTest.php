<?php

namespace Tests\Feature\Integracao;

use App\Enums\TipoSolicitacaoLGPD;
use App\Models\Fiscal;
use App\Models\Fornecedor;
use App\Models\LogLgpdSolicitacao;
use App\Models\Servidor;
use App\Models\User;
use App\Services\LGPDService;
use Tests\TestCase;
use Tests\Traits\RunsTenantMigrations;
use Tests\Traits\SeedsTenantData;

class LgpdFluxoCompletoTest extends TestCase
{
    use RunsTenantMigrations;
    use SeedsTenantData;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpTenant();
        $this->seedBaseData();
        $this->admin = $this->createAdminUser([
            'mfa_secret' => 'TESTSECRETKEY123',
            'mfa_enabled_at' => now(),
            'mfa_recovery_codes' => encrypt(json_encode(['AAAA-BBBB'])),
        ]);
    }

    private function actAsAdmin(): static
    {
        $this->actingAs($this->admin)->withSession(['mfa_verified' => true]);

        return $this;
    }

    // ─── FLUXO ANONIMIZACAO COMPLETO ──────────────────────

    public function test_fluxo_completo_anonimizacao_fornecedor(): void
    {
        $fornecedor = Fornecedor::factory()->create([
            'razao_social' => 'Empresa Teste LGPD LTDA',
            'email' => 'lgpd@teste.com',
        ]);

        // 1. Criar solicitacao de anonimizacao via controller
        $response = $this->actAsAdmin()->post(route('tenant.lgpd.store'), [
            'tipo_solicitacao' => TipoSolicitacaoLGPD::Anonimizacao->value,
            'entidade_tipo' => 'fornecedor',
            'entidade_id' => $fornecedor->id,
            'justificativa' => 'Solicitacao formal do titular conforme art. 18 LGPD.',
        ]);

        $response->assertRedirect(route('tenant.lgpd.index'));
        $response->assertSessionHas('success');

        // 2. Verificar dados foram anonimizados
        $fornecedor->refresh();
        $this->assertStringStartsWith('ANONIMIZADO_', $fornecedor->razao_social);
        $this->assertStringStartsWith('ANONIMIZADO_', $fornecedor->email);

        // 3. Verificar log criado com status processado
        $log = LogLgpdSolicitacao::where('entidade_tipo', Fornecedor::class)
            ->where('entidade_id', $fornecedor->id)
            ->where('status', 'processado')
            ->first();

        $this->assertNotNull($log);
        $this->assertNotNull($log->data_execucao);
        $this->assertNotNull($log->campos_anonimizados);
        $this->assertIsArray($log->campos_anonimizados);

        // 4. Verificar show exibe status processado
        $response = $this->get(route('tenant.lgpd.show', $log));
        $response->assertStatus(200);
        $response->assertSee('Processado');

        // 5. Verificar index exibe na listagem com status correto
        $response = $this->get(route('tenant.lgpd.index'));
        $response->assertStatus(200);
        $response->assertSee('Anonimizado');
    }

    public function test_fluxo_completo_anonimizacao_servidor(): void
    {
        $servidor = Servidor::factory()->create([
            'nome' => 'Servidor LGPD Teste',
            'cpf' => '111.222.333-44',
            'email' => 'servidor@lgpd.gov.br',
        ]);

        $response = $this->actAsAdmin()->post(route('tenant.lgpd.store'), [
            'tipo_solicitacao' => TipoSolicitacaoLGPD::Anonimizacao->value,
            'entidade_tipo' => 'servidor',
            'entidade_id' => $servidor->id,
            'justificativa' => 'Anonimizacao do servidor conforme LGPD.',
        ]);

        $response->assertRedirect(route('tenant.lgpd.index'));

        $servidor->refresh();
        $this->assertStringStartsWith('ANONIMIZADO_', $servidor->nome);
        $this->assertStringStartsWith('ANONIMIZADO_', $servidor->email);
        $this->assertEquals('***.***.***-**', $servidor->cpf);
    }

    public function test_fluxo_completo_anonimizacao_fiscal(): void
    {
        $fiscal = Fiscal::factory()->create([
            'nome' => 'Fiscal LGPD Teste',
            'email' => 'fiscal@lgpd.gov.br',
            'matricula' => '99999',
        ]);

        $response = $this->actAsAdmin()->post(route('tenant.lgpd.store'), [
            'tipo_solicitacao' => TipoSolicitacaoLGPD::Anonimizacao->value,
            'entidade_tipo' => 'fiscal',
            'entidade_id' => $fiscal->id,
            'justificativa' => 'Anonimizacao do fiscal conforme LGPD.',
        ]);

        $response->assertRedirect(route('tenant.lgpd.index'));

        $fiscal->refresh();
        $this->assertStringStartsWith('ANONIMIZADO_', $fiscal->nome);
        $this->assertStringStartsWith('ANONIMIZADO_', $fiscal->email);
        $this->assertEquals('99999', $fiscal->matricula); // matricula preservada
    }

    public function test_fluxo_completo_anonimizacao_usuario_inativo(): void
    {
        $user = $this->createUserWithRole('fiscal_contrato', [
            'is_ativo' => false,
            'nome' => 'Usuario LGPD Inativo',
            'email' => 'inativo@lgpd.gov.br',
        ]);

        $response = $this->actAsAdmin()->post(route('tenant.lgpd.store'), [
            'tipo_solicitacao' => TipoSolicitacaoLGPD::Anonimizacao->value,
            'entidade_tipo' => 'usuario',
            'entidade_id' => $user->id,
            'justificativa' => 'Anonimizacao do usuario desativado.',
        ]);

        $response->assertRedirect(route('tenant.lgpd.index'));

        $user->refresh();
        $this->assertStringStartsWith('ANONIMIZADO_', $user->nome);
        $this->assertStringStartsWith('ANONIMIZADO_', $user->email);
    }

    // ─── FLUXO SOLICITACAO PENDENTE → PROCESSAMENTO MANUAL ─────

    public function test_fluxo_portabilidade_criar_e_processar(): void
    {
        $fornecedor = Fornecedor::factory()->create();

        // 1. Criar solicitacao de portabilidade (fica pendente)
        $response = $this->actAsAdmin()->post(route('tenant.lgpd.store'), [
            'tipo_solicitacao' => TipoSolicitacaoLGPD::Portabilidade->value,
            'entidade_tipo' => 'fornecedor',
            'entidade_id' => $fornecedor->id,
            'justificativa' => 'Titular solicita portabilidade dos dados.',
        ]);

        $response->assertRedirect(route('tenant.lgpd.index'));

        $solicitacao = LogLgpdSolicitacao::latest('id')->first();
        $this->assertEquals('pendente', $solicitacao->status);

        // 2. Verificar show exibe botao processar
        $response = $this->get(route('tenant.lgpd.show', $solicitacao));
        $response->assertStatus(200);
        $response->assertSee('Processar Solicitacao');
        $response->assertSee('Marcar como Processado');

        // 3. Processar a solicitacao
        $response = $this->post(route('tenant.lgpd.processar', $solicitacao), [
            'observacao' => 'Dados exportados em CSV e enviados ao titular via protocolo 001/2026.',
        ]);

        $response->assertRedirect(route('tenant.lgpd.show', $solicitacao));
        $response->assertSessionHas('success');

        // 4. Verificar que novo registro processado foi criado
        $logProcessado = LogLgpdSolicitacao::where('status', 'processado')
            ->where('entidade_id', $fornecedor->id)
            ->where('tipo_solicitacao', TipoSolicitacaoLGPD::Portabilidade->value)
            ->first();

        $this->assertNotNull($logProcessado);
        $this->assertNotNull($logProcessado->data_execucao);
        $this->assertEquals($this->admin->id, $logProcessado->executado_por);

        // 5. Verificar show nao exibe mais o botao processar
        $response = $this->get(route('tenant.lgpd.show', $solicitacao));
        $response->assertStatus(200);
        $response->assertDontSee('Marcar como Processado');
    }

    public function test_fluxo_retificacao_criar_e_processar(): void
    {
        $servidor = Servidor::factory()->create();

        // 1. Criar solicitacao de retificacao
        $this->actAsAdmin()->post(route('tenant.lgpd.store'), [
            'tipo_solicitacao' => TipoSolicitacaoLGPD::Retificacao->value,
            'entidade_tipo' => 'servidor',
            'entidade_id' => $servidor->id,
            'justificativa' => 'Titular solicita correcao de nome.',
        ]);

        $solicitacao = LogLgpdSolicitacao::latest('id')->first();

        // 2. Processar
        $response = $this->post(route('tenant.lgpd.processar', $solicitacao), [
            'observacao' => 'Nome corrigido manualmente de "Joao" para "Joao Carlos" conforme documento apresentado.',
        ]);

        $response->assertRedirect(route('tenant.lgpd.show', $solicitacao));
        $response->assertSessionHas('success');
    }

    public function test_fluxo_revogacao_criar_e_processar(): void
    {
        $fornecedor = Fornecedor::factory()->create();

        $this->actAsAdmin()->post(route('tenant.lgpd.store'), [
            'tipo_solicitacao' => TipoSolicitacaoLGPD::Revogacao->value,
            'entidade_tipo' => 'fornecedor',
            'entidade_id' => $fornecedor->id,
            'justificativa' => 'Titular revoga consentimento para uso de dados.',
        ]);

        $solicitacao = LogLgpdSolicitacao::latest('id')->first();

        $response = $this->post(route('tenant.lgpd.processar', $solicitacao), [
            'observacao' => 'Consentimento revogado. Dados mantidos apenas para obrigacao legal (contratos publicos).',
        ]);

        $response->assertRedirect(route('tenant.lgpd.show', $solicitacao));
        $response->assertSessionHas('success');
    }

    // ─── PROTECOES E RESTRICOES ──────────────────────────

    public function test_anonimizacao_duplicada_bloqueada(): void
    {
        $fornecedor = Fornecedor::factory()->create();

        // Primeira anonimizacao
        $this->actAsAdmin()->post(route('tenant.lgpd.store'), [
            'tipo_solicitacao' => TipoSolicitacaoLGPD::Anonimizacao->value,
            'entidade_tipo' => 'fornecedor',
            'entidade_id' => $fornecedor->id,
            'justificativa' => 'Primeira anonimizacao.',
        ]);

        // Segunda tentativa
        $response = $this->post(route('tenant.lgpd.store'), [
            'tipo_solicitacao' => TipoSolicitacaoLGPD::Anonimizacao->value,
            'entidade_tipo' => 'fornecedor',
            'entidade_id' => $fornecedor->id,
            'justificativa' => 'Tentativa duplicada.',
        ]);

        $response->assertSessionHas('error');
    }

    public function test_usuario_ativo_nao_pode_ser_anonimizado(): void
    {
        $user = $this->createUserWithRole('fiscal_contrato', ['is_ativo' => true]);

        $response = $this->actAsAdmin()->post(route('tenant.lgpd.store'), [
            'tipo_solicitacao' => TipoSolicitacaoLGPD::Anonimizacao->value,
            'entidade_tipo' => 'usuario',
            'entidade_id' => $user->id,
            'justificativa' => 'Tentativa em usuario ativo.',
        ]);

        $response->assertSessionHas('error');
    }

    public function test_usuario_sem_permissao_nao_acessa_lgpd(): void
    {
        $user = $this->createUserWithRole('fiscal_contrato');

        $response = $this->actingAs($user)->get(route('tenant.lgpd.index'));
        $response->assertStatus(403);
    }

    public function test_usuario_sem_permissao_nao_processa_solicitacao(): void
    {
        $fornecedor = Fornecedor::factory()->create();

        // Admin cria solicitacao
        $this->actAsAdmin()->post(route('tenant.lgpd.store'), [
            'tipo_solicitacao' => TipoSolicitacaoLGPD::Exclusao->value,
            'entidade_tipo' => 'fornecedor',
            'entidade_id' => $fornecedor->id,
            'justificativa' => 'Solicitacao de exclusao.',
        ]);

        $solicitacao = LogLgpdSolicitacao::latest('id')->first();

        // Usuario sem permissao tenta processar
        $user = $this->createUserWithRole('controladoria');
        $response = $this->actingAs($user)
            ->withSession(['mfa_verified' => true])
            ->post(route('tenant.lgpd.processar', $solicitacao), [
                'observacao' => 'Tentativa sem permissao.',
            ]);

        $response->assertStatus(403);
    }

    // ─── VERIFICACAO SERVICO LGPD ──────────────────────

    public function test_ja_anonimizado_retorna_true_apos_fluxo_completo(): void
    {
        $fornecedor = Fornecedor::factory()->create();

        $this->assertFalse(LGPDService::jaAnonimizado($fornecedor));

        $this->actAsAdmin()->post(route('tenant.lgpd.store'), [
            'tipo_solicitacao' => TipoSolicitacaoLGPD::Anonimizacao->value,
            'entidade_tipo' => 'fornecedor',
            'entidade_id' => $fornecedor->id,
            'justificativa' => 'Fluxo completo via UI.',
        ]);

        $this->assertTrue(LGPDService::jaAnonimizado($fornecedor));
    }
}
