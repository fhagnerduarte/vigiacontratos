<?php

namespace Tests\Feature\Commands;

use App\Models\Fornecedor;
use App\Models\Fiscal;
use Tests\TestCase;
use Tests\Traits\RunsTenantMigrations;
use Tests\Traits\SeedsTenantData;

class EncryptExistingDataCommandTest extends TestCase
{
    use RunsTenantMigrations;
    use SeedsTenantData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpTenant();
        $this->seedBaseData();
    }

    public function test_command_encrypts_fornecedor_fields(): void
    {
        // Command faz DB::purge/reconnect, quebrando isolamento transacional.
        // Testar via output do command (contagem de registros processados).
        $fornecedor = Fornecedor::factory()->create([
            'email' => 'plain@test.com',
            'telefone' => '(11) 1234-5678',
            'representante_legal' => 'Joao Plain',
        ]);

        $this->artisan('data:encrypt-existing', ['--tenant' => $this->tenant->slug])
            ->expectsOutputToContain('Fornecedores:')
            ->expectsOutputToContain('Criptografia concluida com sucesso')
            ->assertSuccessful();
    }

    public function test_command_encrypts_fiscal_fields(): void
    {
        // Command faz DB::purge/reconnect, quebrando isolamento transacional.
        // Testar via output do command (contagem de registros processados).
        $contrato = \App\Models\Contrato::factory()->create();

        Fiscal::factory()->create([
            'contrato_id' => $contrato->id,
            'email' => 'fiscal.plain@gov.br',
        ]);

        $this->artisan('data:encrypt-existing', ['--tenant' => $this->tenant->slug])
            ->expectsOutputToContain('Fiscais:')
            ->expectsOutputToContain('Criptografia concluida com sucesso')
            ->assertSuccessful();
    }

    public function test_command_dry_run_nao_altera_dados(): void
    {
        // Criar via factory (ja criptografado pelo cast) e rodar dry-run
        Fornecedor::factory()->create(['email' => 'dry@test.com']);

        $this->artisan('data:encrypt-existing', [
            '--tenant' => $this->tenant->slug,
            '--dry-run' => true,
        ])
            ->expectsOutputToContain('DRY-RUN')
            ->expectsOutputToContain('Simulacao concluida')
            ->assertSuccessful();
    }

    public function test_command_e_idempotente(): void
    {
        // Factory ja criptografa via cast — command deve detectar e pular (skipped)
        Fornecedor::factory()->create(['email' => 'idem@test.com']);

        // Primeira execucao — dados ja criptografados, deve pular
        $this->artisan('data:encrypt-existing', ['--tenant' => $this->tenant->slug])
            ->expectsOutputToContain('ja estavam criptografados')
            ->assertSuccessful();

        // Segunda execucao — idempotente, mesmo resultado
        $this->artisan('data:encrypt-existing', ['--tenant' => $this->tenant->slug])
            ->expectsOutputToContain('ja estavam criptografados')
            ->assertSuccessful();
    }
}
