<?php

namespace Tests\Feature\Controllers;

use App\Enums\CategoriaContrato;
use App\Enums\ModalidadeContratacao;
use App\Enums\NivelRisco;
use App\Enums\StatusContrato;
use App\Enums\TipoContrato;
use App\Enums\TipoPagamento;
use App\Models\Contrato;
use App\Models\Fornecedor;
use App\Models\Secretaria;
use App\Models\Servidor;
use App\Models\User;
use Tests\TestCase;
use Tests\Traits\RunsTenantMigrations;
use Tests\Traits\SeedsTenantData;

class ContratosControllerTest extends TestCase
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

    protected function actAsAdmin(): self
    {
        return $this->actingAs($this->admin)->withSession(['mfa_verified' => true]);
    }

    protected function dadosContratoValidos(array $overrides = []): array
    {
        $fornecedor = Fornecedor::factory()->create();
        $secretaria = Secretaria::factory()->create();
        $servidor = Servidor::factory()->create(['secretaria_id' => $secretaria->id]);
        $fiscalServidor = Servidor::factory()->create(['secretaria_id' => $secretaria->id]);

        return array_merge([
            'ano' => date('Y'),
            'objeto' => 'Contrato de prestação de serviços de limpeza',
            'tipo' => TipoContrato::Servico->value,
            'modalidade_contratacao' => ModalidadeContratacao::PregaoEletronico->value,
            'fornecedor_id' => $fornecedor->id,
            'secretaria_id' => $secretaria->id,
            'servidor_id' => $servidor->id,
            'unidade_gestora' => 'Prefeitura Municipal',
            'data_inicio' => now()->format('Y-m-d'),
            'data_fim' => now()->addYear()->format('Y-m-d'),
            'valor_global' => '120000.00',
            'valor_mensal' => '10000.00',
            'tipo_pagamento' => TipoPagamento::Mensal->value,
            'fonte_recurso' => 'Recursos Próprios',
            'dotacao_orcamentaria' => '01.02.03.004.0005.1.234.56',
            'numero_empenho' => '0001/2026',
            'numero_processo' => '12345/2026',
            'fiscal_servidor_id' => $fiscalServidor->id,
        ], $overrides);
    }

    // ─── INDEX ─────────────────────────────────────────────

    public function test_index_exibe_listagem_de_contratos(): void
    {
        Contrato::factory()->count(3)->create();

        $response = $this->actAsAdmin()->get(route('tenant.contratos.index'));

        $response->assertStatus(200);
        $response->assertViewIs('tenant.contratos.index');
        $response->assertViewHas('contratos');
    }

    public function test_index_requer_autenticacao(): void
    {
        $response = $this->get(route('tenant.contratos.index'));
        $response->assertRedirect();
    }

    public function test_index_usuario_sem_permissao_retorna_403(): void
    {
        $role = \App\Models\Role::factory()->create(['nome' => 'role_sem_permissao']);
        $user = User::factory()->create(['role_id' => $role->id]);
        $response = $this->actingAs($user)->get(route('tenant.contratos.index'));
        $response->assertStatus(403);
    }

    public function test_index_filtra_por_status(): void
    {
        Contrato::factory()->vigente()->create();
        Contrato::factory()->vencido()->create();

        $response = $this->actAsAdmin()->get(route('tenant.contratos.index', ['status' => 'vigente']));

        $response->assertStatus(200);
    }

    public function test_index_filtra_por_secretaria(): void
    {
        $secretaria = Secretaria::factory()->create();
        Contrato::factory()->create(['secretaria_id' => $secretaria->id]);

        $response = $this->actAsAdmin()->get(route('tenant.contratos.index', ['secretaria_id' => $secretaria->id]));

        $response->assertStatus(200);
    }

    public function test_index_filtra_por_nivel_risco(): void
    {
        Contrato::factory()->altoRisco()->create();

        $response = $this->actAsAdmin()->get(route('tenant.contratos.index', ['nivel_risco' => NivelRisco::Alto->value]));

        $response->assertStatus(200);
    }

    // ─── CREATE ────────────────────────────────────────────

    public function test_create_exibe_formulario_wizard(): void
    {
        Fornecedor::factory()->create();
        Secretaria::factory()->create();

        $response = $this->actAsAdmin()->get(route('tenant.contratos.create'));

        $response->assertStatus(200);
        $response->assertViewIs('tenant.contratos.create');
        $response->assertViewHas('fornecedores');
        $response->assertViewHas('secretarias');
        $response->assertViewHas('servidores');
    }

    // ─── STORE ─────────────────────────────────────────────

    public function test_store_cria_contrato_com_sucesso(): void
    {
        $dados = $this->dadosContratoValidos();

        $response = $this->actAsAdmin()->post(route('tenant.contratos.store'), $dados);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('contratos', [
            'objeto' => $dados['objeto'],
            'status' => StatusContrato::Vigente->value,
        ], 'tenant');
    }

    public function test_store_valida_objeto_obrigatorio(): void
    {
        $dados = $this->dadosContratoValidos(['objeto' => '']);

        $response = $this->actAsAdmin()->post(route('tenant.contratos.store'), $dados);

        $response->assertSessionHasErrors('objeto');
    }

    public function test_store_valida_fornecedor_existente(): void
    {
        $dados = $this->dadosContratoValidos(['fornecedor_id' => 9999]);

        $response = $this->actAsAdmin()->post(route('tenant.contratos.store'), $dados);

        $response->assertSessionHasErrors('fornecedor_id');
    }

    public function test_store_valida_data_fim_apos_data_inicio(): void
    {
        $dados = $this->dadosContratoValidos([
            'data_inicio' => '2026-06-01',
            'data_fim' => '2026-01-01',
        ]);

        $response = $this->actAsAdmin()->post(route('tenant.contratos.store'), $dados);

        $response->assertSessionHasErrors('data_fim');
    }

    public function test_store_valida_valor_global_positivo(): void
    {
        $dados = $this->dadosContratoValidos(['valor_global' => '-1000']);

        $response = $this->actAsAdmin()->post(route('tenant.contratos.store'), $dados);

        $response->assertSessionHasErrors('valor_global');
    }

    public function test_store_dispensa_exige_fundamento_legal(): void
    {
        $dados = $this->dadosContratoValidos([
            'modalidade_contratacao' => ModalidadeContratacao::Dispensa->value,
        ]);
        unset($dados['fundamento_legal']);

        $response = $this->actAsAdmin()->post(route('tenant.contratos.store'), $dados);

        $response->assertSessionHasErrors('fundamento_legal');
    }

    public function test_store_cria_fiscal_junto_com_contrato(): void
    {
        $dados = $this->dadosContratoValidos();

        $this->actAsAdmin()->post(route('tenant.contratos.store'), $dados);

        $contrato = Contrato::latest('id')->first();
        $this->assertNotNull($contrato);
        $this->assertTrue($contrato->fiscais()->exists());
    }

    // ─── SHOW ──────────────────────────────────────────────

    public function test_show_exibe_detalhes_do_contrato(): void
    {
        $contrato = Contrato::factory()->create();

        $response = $this->actAsAdmin()->get(route('tenant.contratos.show', $contrato));

        $response->assertStatus(200);
        $response->assertViewIs('tenant.contratos.show');
        $response->assertViewHas('contrato');
    }

    // ─── EDIT ──────────────────────────────────────────────

    public function test_edit_exibe_formulario_preenchido(): void
    {
        $contrato = Contrato::factory()->vigente()->create();

        $response = $this->actAsAdmin()->get(route('tenant.contratos.edit', $contrato));

        $response->assertStatus(200);
        $response->assertViewIs('tenant.contratos.edit');
        $response->assertViewHas('contrato');
    }

    public function test_edit_contrato_vencido_bloqueia_edicao(): void
    {
        $contrato = Contrato::factory()->vencido()->create();

        $response = $this->actAsAdmin()->get(route('tenant.contratos.edit', $contrato));

        $response->assertRedirect(route('tenant.contratos.show', $contrato));
        $response->assertSessionHas('error');
    }

    // ─── UPDATE ────────────────────────────────────────────

    public function test_update_atualiza_contrato_com_sucesso(): void
    {
        $contrato = Contrato::factory()->vigente()->create([
            'tipo' => TipoContrato::Servico,
            'modalidade_contratacao' => ModalidadeContratacao::PregaoEletronico,
        ]);

        $dadosUpdate = [
            'objeto' => 'Objeto Atualizado',
            'tipo' => $contrato->tipo->value,
            'modalidade_contratacao' => $contrato->modalidade_contratacao->value,
            'fornecedor_id' => $contrato->fornecedor_id,
            'secretaria_id' => $contrato->secretaria_id,
            'servidor_id' => $contrato->servidor_id,
            'data_inicio' => $contrato->data_inicio->format('Y-m-d'),
            'data_fim' => $contrato->data_fim->format('Y-m-d'),
            'valor_global' => $contrato->valor_global,
            'valor_mensal' => $contrato->valor_mensal,
            'tipo_pagamento' => $contrato->tipo_pagamento->value,
            'fonte_recurso' => $contrato->fonte_recurso,
            'dotacao_orcamentaria' => $contrato->dotacao_orcamentaria,
            'numero_empenho' => $contrato->numero_empenho,
            'numero_processo' => $contrato->numero_processo ?? '12345/2026',
        ];

        $response = $this->actAsAdmin()->put(route('tenant.contratos.update', $contrato), $dadosUpdate);

        $response->assertRedirect(route('tenant.contratos.show', $contrato));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('contratos', [
            'id' => $contrato->id,
            'objeto' => 'Objeto Atualizado',
        ], 'tenant');
    }

    public function test_update_registra_historico_de_alteracoes(): void
    {
        $contrato = Contrato::factory()->vigente()->create([
            'tipo' => TipoContrato::Servico,
            'modalidade_contratacao' => ModalidadeContratacao::PregaoEletronico,
        ]);

        $response = $this->actAsAdmin()->put(route('tenant.contratos.update', $contrato), [
            'objeto' => 'Objeto Modificado para Teste de Auditoria',
            'tipo' => $contrato->tipo->value,
            'modalidade_contratacao' => $contrato->modalidade_contratacao->value,
            'fornecedor_id' => $contrato->fornecedor_id,
            'secretaria_id' => $contrato->secretaria_id,
            'servidor_id' => $contrato->servidor_id,
            'data_inicio' => $contrato->data_inicio->format('Y-m-d'),
            'data_fim' => $contrato->data_fim->format('Y-m-d'),
            'valor_global' => $contrato->valor_global,
            'valor_mensal' => $contrato->valor_mensal,
            'tipo_pagamento' => $contrato->tipo_pagamento->value,
            'fonte_recurso' => $contrato->fonte_recurso,
            'dotacao_orcamentaria' => $contrato->dotacao_orcamentaria,
            'numero_empenho' => $contrato->numero_empenho,
            'numero_processo' => $contrato->numero_processo ?? '12345/2026',
        ]);

        $response->assertRedirect(route('tenant.contratos.show', $contrato));
        $response->assertSessionHas('success');

        $contrato->refresh();
        $this->assertTrue($contrato->historicoAlteracoes()->exists());
    }

    // ─── DESTROY ───────────────────────────────────────────

    public function test_destroy_soft_deleta_contrato(): void
    {
        $contrato = Contrato::factory()->create([
            'numero' => '999/' . date('Y'),
        ]);

        $response = $this->actAsAdmin()->delete(route('tenant.contratos.destroy', $contrato));

        $response->assertRedirect(route('tenant.contratos.index'));
        $this->assertSoftDeleted($contrato);
    }
}
