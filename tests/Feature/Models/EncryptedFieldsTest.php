<?php

namespace Tests\Feature\Models;

use App\Models\Fiscal;
use App\Models\Fornecedor;
use App\Models\LoginLog;
use App\Models\User;
use Tests\TestCase;
use Tests\Traits\RunsTenantMigrations;
use Tests\Traits\SeedsTenantData;

class EncryptedFieldsTest extends TestCase
{
    use RunsTenantMigrations;
    use SeedsTenantData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpTenant();
        $this->seedBaseData();
    }

    public function test_fornecedor_email_e_criptografado_no_banco(): void
    {
        $fornecedor = Fornecedor::factory()->create(['email' => 'teste@empresa.com']);

        $raw = $this->getRawValue('fornecedores', $fornecedor->id, 'email');
        $this->assertNotEquals('teste@empresa.com', $raw);
        $this->assertEquals('teste@empresa.com', $fornecedor->fresh()->email);
    }

    public function test_fornecedor_telefone_e_criptografado_no_banco(): void
    {
        $fornecedor = Fornecedor::factory()->create(['telefone' => '(11) 99999-0000']);

        $raw = $this->getRawValue('fornecedores', $fornecedor->id, 'telefone');
        $this->assertNotEquals('(11) 99999-0000', $raw);
        $this->assertEquals('(11) 99999-0000', $fornecedor->fresh()->telefone);
    }

    public function test_fornecedor_representante_legal_e_criptografado_no_banco(): void
    {
        $fornecedor = Fornecedor::factory()->create(['representante_legal' => 'Joao da Silva']);

        $raw = $this->getRawValue('fornecedores', $fornecedor->id, 'representante_legal');
        $this->assertNotEquals('Joao da Silva', $raw);
        $this->assertEquals('Joao da Silva', $fornecedor->fresh()->representante_legal);
    }

    public function test_fiscal_email_e_criptografado_no_banco(): void
    {
        $user = $this->createAdminUser();
        $contrato = \App\Models\Contrato::factory()->create();
        $fiscal = Fiscal::factory()->create([
            'contrato_id' => $contrato->id,
            'email' => 'fiscal@prefeitura.gov.br',
        ]);

        $raw = $this->getRawValue('fiscais', $fiscal->id, 'email');
        $this->assertNotEquals('fiscal@prefeitura.gov.br', $raw);
        $this->assertEquals('fiscal@prefeitura.gov.br', $fiscal->fresh()->email);
    }

    public function test_login_log_ip_address_e_criptografado(): void
    {
        $user = $this->createAdminUser();

        $log = LoginLog::create([
            'user_id' => $user->id,
            'tenant_id' => $this->tenant->id,
            'ip_address' => '192.168.1.100',
            'user_agent' => 'Mozilla/5.0 Test',
            'success' => true,
        ]);

        $raw = $this->getRawValue('login_logs', $log->id, 'ip_address');
        $this->assertNotEquals('192.168.1.100', $raw);

        $fresh = LoginLog::find($log->id);
        $this->assertEquals('192.168.1.100', $fresh->ip_address);
    }

    public function test_login_log_user_agent_e_criptografado(): void
    {
        $user = $this->createAdminUser();

        $log = LoginLog::create([
            'user_id' => $user->id,
            'tenant_id' => $this->tenant->id,
            'ip_address' => '10.0.0.1',
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0)',
            'success' => true,
        ]);

        $raw = $this->getRawValue('login_logs', $log->id, 'user_agent');
        $this->assertNotEquals('Mozilla/5.0 (Windows NT 10.0)', $raw);

        $fresh = LoginLog::find($log->id);
        $this->assertEquals('Mozilla/5.0 (Windows NT 10.0)', $fresh->user_agent);
    }

    public function test_campo_criptografado_e_legivel_via_model(): void
    {
        $fornecedor = Fornecedor::factory()->create([
            'email' => 'round@trip.com',
            'telefone' => '(21) 3333-4444',
            'representante_legal' => 'Maria Teste',
        ]);

        $loaded = Fornecedor::find($fornecedor->id);
        $this->assertEquals('round@trip.com', $loaded->email);
        $this->assertEquals('(21) 3333-4444', $loaded->telefone);
        $this->assertEquals('Maria Teste', $loaded->representante_legal);
    }

    public function test_valor_no_banco_e_diferente_do_plaintext(): void
    {
        $fornecedor = Fornecedor::factory()->create(['email' => 'plaintext@test.com']);

        $raw = $this->getRawValue('fornecedores', $fornecedor->id, 'email');

        // Valor criptografado comeca com 'eyJ' (base64 JSON do Laravel encryption)
        $this->assertTrue(
            str_starts_with($raw, 'eyJ'),
            "Valor no banco deveria ser criptografado (base64 JSON), obteve: " . substr($raw, 0, 30)
        );
    }

    /**
     * Helper: busca o valor raw (sem decrypt) diretamente do banco.
     */
    private function getRawValue(string $table, int $id, string $column): string
    {
        $row = \Illuminate\Support\Facades\DB::connection('tenant')
            ->table($table)
            ->where('id', $id)
            ->first([$column]);

        return $row->{$column} ?? '';
    }
}
