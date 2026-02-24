<?php

namespace Tests\Feature\LGPD;

use App\Enums\TipoSolicitacaoLGPD;
use App\Models\Fiscal;
use App\Models\Fornecedor;
use App\Models\LogLgpdSolicitacao;
use App\Models\Servidor;
use App\Models\User;
use App\Services\LGPDService;
use Illuminate\Database\QueryException;
use Tests\TestCase;
use Tests\Traits\RunsTenantMigrations;
use Tests\Traits\SeedsTenantData;

class LGPDServiceTest extends TestCase
{
    use RunsTenantMigrations;
    use SeedsTenantData;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpTenant();
        $this->seedBaseData();
        $this->admin = $this->createAdminUser();
    }

    // ─── ANONIMIZAR FORNECEDOR ──────────────────────────

    public function test_anonimizar_fornecedor_substitui_campos_pii(): void
    {
        $fornecedor = Fornecedor::factory()->create([
            'razao_social' => 'Empresa Teste LTDA',
            'nome_fantasia' => 'Teste Corp',
            'representante_legal' => 'Joao Silva',
            'email' => 'joao@teste.com',
            'telefone' => '(11) 99999-0000',
            'endereco' => 'Rua das Flores, 123',
            'cep' => '01001-000',
            'cnpj' => '12.345.678/0001-90',
        ]);

        LGPDService::anonimizarFornecedor($fornecedor, 'Protocolo 001/2026', 'Solicitacao formal', $this->admin);

        $fornecedor->refresh();

        $this->assertStringStartsWith('ANONIMIZADO_', $fornecedor->razao_social);
        $this->assertStringStartsWith('ANONIMIZADO_', $fornecedor->nome_fantasia);
        $this->assertStringStartsWith('ANONIMIZADO_', $fornecedor->representante_legal);
        $this->assertStringStartsWith('ANONIMIZADO_', $fornecedor->email);
        $this->assertStringStartsWith('ANONIMIZADO_', $fornecedor->telefone);
        $this->assertStringStartsWith('ANONIMIZADO_', $fornecedor->endereco);
        // CEP usa mascara (campo curto): '***.**-***'
        $this->assertEquals('***.**-***', $fornecedor->cep);
    }

    public function test_anonimizar_fornecedor_preserva_cnpj(): void
    {
        $fornecedor = Fornecedor::factory()->create();

        $cnpjOriginal = $fornecedor->cnpj;

        LGPDService::anonimizarFornecedor($fornecedor, 'Protocolo 002/2026');

        $fornecedor->refresh();

        $this->assertEquals($cnpjOriginal, $fornecedor->cnpj);
    }

    public function test_anonimizar_fornecedor_cria_log_lgpd(): void
    {
        $fornecedor = Fornecedor::factory()->create();

        $log = LGPDService::anonimizarFornecedor($fornecedor, 'Protocolo 003/2026', 'Teste', $this->admin);

        $this->assertDatabaseHas('log_lgpd_solicitacoes', [
            'id' => $log->id,
            'tipo_solicitacao' => TipoSolicitacaoLGPD::Anonimizacao->value,
            'entidade_tipo' => Fornecedor::class,
            'entidade_id' => $fornecedor->id,
            'solicitante' => 'Protocolo 003/2026',
            'status' => 'processado',
            'executado_por' => $this->admin->id,
        ]);

        $this->assertNotNull($log->campos_anonimizados);
        $this->assertIsArray($log->campos_anonimizados);
    }

    public function test_anonimizar_fornecedor_formato_anonimizado_correto(): void
    {
        $fornecedor = Fornecedor::factory()->create([
            'razao_social' => 'Empresa ABC',
        ]);

        LGPDService::anonimizarFornecedor($fornecedor, 'Protocolo 004/2026');

        $fornecedor->refresh();

        // Formato: ANONIMIZADO_ + 8 chars hex
        $this->assertMatchesRegularExpression('/^ANONIMIZADO_[a-f0-9]{8}$/', $fornecedor->razao_social);
    }

    // ─── ANONIMIZAR FISCAL ──────────────────────────────

    public function test_anonimizar_fiscal_substitui_nome_email(): void
    {
        $fiscal = Fiscal::factory()->create([
            'nome' => 'Maria Santos',
            'email' => 'maria@prefeitura.gov.br',
            'matricula' => '12345',
        ]);

        LGPDService::anonimizarFiscal($fiscal, 'Protocolo 005/2026');

        $fiscal->refresh();

        $this->assertStringStartsWith('ANONIMIZADO_', $fiscal->nome);
        $this->assertStringStartsWith('ANONIMIZADO_', $fiscal->email);
    }

    public function test_anonimizar_fiscal_preserva_matricula(): void
    {
        $fiscal = Fiscal::factory()->create([
            'matricula' => '12345',
        ]);

        LGPDService::anonimizarFiscal($fiscal, 'Protocolo 006/2026');

        $fiscal->refresh();

        $this->assertEquals('12345', $fiscal->matricula);
    }

    // ─── ANONIMIZAR SERVIDOR ─────────────────────────────

    public function test_anonimizar_servidor_substitui_campos_pii(): void
    {
        $servidor = Servidor::factory()->create([
            'nome' => 'Carlos Oliveira',
            'cpf' => '123.456.789-00',
            'email' => 'carlos@prefeitura.gov.br',
            'telefone' => '(11) 98765-4321',
            'matricula' => '54321',
        ]);

        LGPDService::anonimizarServidor($servidor, 'Protocolo 007/2026');

        $servidor->refresh();

        $this->assertStringStartsWith('ANONIMIZADO_', $servidor->nome);
        $this->assertStringStartsWith('ANONIMIZADO_', $servidor->email);
        $this->assertStringStartsWith('ANONIMIZADO_', $servidor->telefone);
        // CPF usa mascara (campo curto)
        $this->assertEquals('***.***.***-**', $servidor->cpf);
        // matricula preservada
        $this->assertEquals('54321', $servidor->matricula);
    }

    // ─── ANONIMIZAR USUARIO ─────────────────────────────

    public function test_anonimizar_usuario_ativo_lanca_excecao(): void
    {
        $user = $this->createUserWithRole('fiscal_contrato', ['is_ativo' => true]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Nao e possivel anonimizar usuario ativo');

        LGPDService::anonimizarUsuario($user, 'Protocolo 008/2026');
    }

    public function test_anonimizar_usuario_desativado_funciona(): void
    {
        $user = $this->createUserWithRole('fiscal_contrato', [
            'is_ativo' => false,
            'nome' => 'Usuario Inativo',
            'email' => 'inativo@prefeitura.gov.br',
        ]);

        $log = LGPDService::anonimizarUsuario($user, 'Protocolo 009/2026');

        $user->refresh();

        $this->assertStringStartsWith('ANONIMIZADO_', $user->nome);
        $this->assertStringStartsWith('ANONIMIZADO_', $user->email);
        $this->assertEquals('processado', $log->status);
    }

    // ─── PROTECOES ──────────────────────────────────────

    public function test_anonimizar_entidade_ja_anonimizada_lanca_excecao(): void
    {
        $fornecedor = Fornecedor::factory()->create();

        LGPDService::anonimizarFornecedor($fornecedor, 'Protocolo 010/2026');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('ja foi anonimizada anteriormente');

        LGPDService::anonimizarFornecedor($fornecedor, 'Protocolo 011/2026');
    }

    public function test_log_lgpd_e_imutavel_update_lanca_excecao(): void
    {
        $fornecedor = Fornecedor::factory()->create();

        $log = LGPDService::anonimizarFornecedor($fornecedor, 'Protocolo 012/2026');

        $this->expectException(\RuntimeException::class);

        $log->update(['status' => 'cancelado']);
    }

    public function test_log_lgpd_e_imutavel_delete_lanca_excecao(): void
    {
        $fornecedor = Fornecedor::factory()->create();

        $log = LGPDService::anonimizarFornecedor($fornecedor, 'Protocolo 013/2026');

        $this->expectException(\RuntimeException::class);

        $log->delete();
    }

    // ─── FORMATO E HELPERS ──────────────────────────────

    public function test_gerar_valor_anonimizado_formato_correto(): void
    {
        $resultado = LGPDService::gerarValorAnonimizado('Joao Silva');

        $this->assertStringStartsWith('ANONIMIZADO_', $resultado);
        $this->assertEquals(20, strlen($resultado)); // ANONIMIZADO_ (12) + hash (8) = 20
    }

    public function test_gerar_valor_anonimizado_deterministico(): void
    {
        $resultado1 = LGPDService::gerarValorAnonimizado('Joao Silva');
        $resultado2 = LGPDService::gerarValorAnonimizado('Joao Silva');

        $this->assertEquals($resultado1, $resultado2);
    }

    public function test_gerar_valor_anonimizado_diferentes_para_inputs_diferentes(): void
    {
        $resultado1 = LGPDService::gerarValorAnonimizado('Joao Silva');
        $resultado2 = LGPDService::gerarValorAnonimizado('Maria Santos');

        $this->assertNotEquals($resultado1, $resultado2);
    }

    public function test_ja_anonimizado_retorna_true_apos_anonimizacao(): void
    {
        $fornecedor = Fornecedor::factory()->create();

        $this->assertFalse(LGPDService::jaAnonimizado($fornecedor));

        LGPDService::anonimizarFornecedor($fornecedor, 'Protocolo 014/2026');

        $this->assertTrue(LGPDService::jaAnonimizado($fornecedor));
    }

    public function test_ja_anonimizado_retorna_false_antes_anonimizacao(): void
    {
        $fornecedor = Fornecedor::factory()->create();

        $this->assertFalse(LGPDService::jaAnonimizado($fornecedor));
    }
}
