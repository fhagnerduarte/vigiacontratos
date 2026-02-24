<?php

namespace Tests\Feature\Commands;

use App\Models\Fornecedor;
use App\Models\Servidor;
use App\Models\User;
use App\Services\LGPDService;
use Tests\TestCase;
use Tests\Traits\RunsTenantMigrations;
use Tests\Traits\SeedsTenantData;

class LgpdProcessarCommandTest extends TestCase
{
    use RunsTenantMigrations;
    use SeedsTenantData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpTenant();
        $this->seedBaseData();
    }

    // ─── VALIDACOES DE PARAMETROS ────────────────────────

    public function test_command_exige_solicitante(): void
    {
        $this->artisan('lgpd:processar', [
            '--entidade' => 'fornecedor',
            '--id' => '1',
        ])
            ->expectsOutput('A opcao --solicitante e obrigatoria.')
            ->assertExitCode(1);
    }

    public function test_command_exige_entidade_e_id(): void
    {
        $this->artisan('lgpd:processar', [
            '--solicitante' => 'Protocolo 001',
        ])
            ->expectsOutput('As opcoes --entidade e --id sao obrigatorias.')
            ->assertExitCode(1);
    }

    public function test_command_rejeita_entidade_invalida(): void
    {
        $this->artisan('lgpd:processar', [
            '--entidade' => 'contrato',
            '--id' => '1',
            '--solicitante' => 'Protocolo 001',
        ])
            ->expectsOutputToContain('Entidade invalida')
            ->assertExitCode(1);
    }

    public function test_command_entidade_nao_encontrada(): void
    {
        $this->artisan('lgpd:processar', [
            '--entidade' => 'fornecedor',
            '--id' => '99999',
            '--solicitante' => 'Protocolo 001',
        ])
            ->expectsOutputToContain('nao encontrado')
            ->assertExitCode(1);
    }

    // ─── DRY-RUN ─────────────────────────────────────────

    public function test_command_dry_run_nao_modifica_dados(): void
    {
        $fornecedor = Fornecedor::factory()->create([
            'razao_social' => 'Empresa Original',
        ]);

        $this->artisan('lgpd:processar', [
            '--entidade' => 'fornecedor',
            '--id' => (string) $fornecedor->id,
            '--solicitante' => 'Protocolo 001',
            '--dry-run' => true,
        ])
            ->expectsOutputToContain('[DRY-RUN]')
            ->assertExitCode(0);

        $fornecedor->refresh();
        $this->assertEquals('Empresa Original', $fornecedor->razao_social);
    }

    // ─── EXECUCAO COM SUCESSO ────────────────────────────

    public function test_command_anonimiza_fornecedor_com_sucesso(): void
    {
        $fornecedor = Fornecedor::factory()->create([
            'razao_social' => 'Empresa ABC LTDA',
            'email' => 'contato@empresa.com',
        ]);

        $this->artisan('lgpd:processar', [
            '--entidade' => 'fornecedor',
            '--id' => (string) $fornecedor->id,
            '--solicitante' => 'Protocolo 001/2026',
            '--justificativa' => 'Solicitacao formal do titular',
        ])
            ->expectsOutputToContain('Anonimizado com sucesso')
            ->assertExitCode(0);

        $fornecedor->refresh();
        $this->assertStringStartsWith('ANONIMIZADO_', $fornecedor->razao_social);
        $this->assertStringStartsWith('ANONIMIZADO_', $fornecedor->email);
    }

    // ─── ENTIDADE JA ANONIMIZADA ─────────────────────────

    public function test_command_entidade_ja_anonimizada(): void
    {
        $fornecedor = Fornecedor::factory()->create();
        LGPDService::anonimizarFornecedor($fornecedor, 'Protocolo anterior');

        $this->artisan('lgpd:processar', [
            '--entidade' => 'fornecedor',
            '--id' => (string) $fornecedor->id,
            '--solicitante' => 'Protocolo novo',
        ])
            ->expectsOutputToContain('ja foi anonimizado')
            ->assertExitCode(0);
    }

    // ─── USUARIO ATIVO (ERRO) ────────────────────────────

    public function test_command_usuario_ativo_falha(): void
    {
        $user = $this->createUserWithRole('fiscal_contrato', ['is_ativo' => true]);

        $this->artisan('lgpd:processar', [
            '--entidade' => 'usuario',
            '--id' => (string) $user->id,
            '--solicitante' => 'Protocolo 001',
        ])
            ->expectsOutputToContain('Erro ao anonimizar')
            ->assertExitCode(1);
    }
}
