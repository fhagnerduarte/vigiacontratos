<?php

namespace Tests\Unit\Models;

use App\Models\Role;
use App\Models\User;
use Tests\TestCase;
use Tests\Traits\RunsTenantMigrations;

class UserMfaTest extends TestCase
{
    use RunsTenantMigrations;

    public function test_is_mfa_enabled_retorna_false_sem_mfa(): void
    {
        $role = Role::factory()->create();
        $user = User::factory()->create(['role_id' => $role->id]);

        $this->assertFalse($user->isMfaEnabled());
    }

    public function test_is_mfa_enabled_retorna_true_com_mfa(): void
    {
        $role = Role::factory()->create();
        $user = User::factory()->create([
            'role_id' => $role->id,
            'mfa_enabled_at' => now(),
            'mfa_secret' => 'TESTSECRETKEY123',
        ]);

        $this->assertTrue($user->isMfaEnabled());
    }

    public function test_is_mfa_required_para_administrador_geral(): void
    {
        $role = Role::on('tenant')->firstOrCreate(['nome' => 'administrador_geral'], ['descricao' => 'Administrador Geral', 'is_padrao' => true, 'is_ativo' => true]);
        $user = User::factory()->create(['role_id' => $role->id]);

        $this->assertTrue($user->isMfaRequired());
    }

    public function test_is_mfa_required_para_controladoria(): void
    {
        $role = Role::on('tenant')->firstOrCreate(['nome' => 'controladoria'], ['descricao' => 'Controladoria', 'is_padrao' => true, 'is_ativo' => true]);
        $user = User::factory()->create(['role_id' => $role->id]);

        $this->assertTrue($user->isMfaRequired());
    }

    public function test_is_mfa_required_false_para_fiscal(): void
    {
        $role = Role::on('tenant')->firstOrCreate(['nome' => 'fiscal_contrato'], ['descricao' => 'Fiscal', 'is_padrao' => true, 'is_ativo' => true]);
        $user = User::factory()->create(['role_id' => $role->id]);

        $this->assertFalse($user->isMfaRequired());
    }

    public function test_is_mfa_optional_para_secretario(): void
    {
        $role = Role::on('tenant')->firstOrCreate(['nome' => 'secretario'], ['descricao' => 'Secretario', 'is_padrao' => true, 'is_ativo' => true]);
        $user = User::factory()->create(['role_id' => $role->id]);

        $this->assertTrue($user->isMfaOptional());
    }

    public function test_is_mfa_optional_para_gestor_contrato(): void
    {
        $role = Role::on('tenant')->firstOrCreate(['nome' => 'gestor_contrato'], ['descricao' => 'Gestor', 'is_padrao' => true, 'is_ativo' => true]);
        $user = User::factory()->create(['role_id' => $role->id]);

        $this->assertTrue($user->isMfaOptional());
    }

    public function test_is_mfa_optional_para_procuradoria(): void
    {
        $role = Role::on('tenant')->firstOrCreate(['nome' => 'procuradoria'], ['descricao' => 'Procuradoria', 'is_padrao' => true, 'is_ativo' => true]);
        $user = User::factory()->create(['role_id' => $role->id]);

        $this->assertTrue($user->isMfaOptional());
    }

    public function test_is_mfa_optional_para_financeiro(): void
    {
        $role = Role::on('tenant')->firstOrCreate(['nome' => 'financeiro'], ['descricao' => 'Financeiro', 'is_padrao' => true, 'is_ativo' => true]);
        $user = User::factory()->create(['role_id' => $role->id]);

        $this->assertTrue($user->isMfaOptional());
    }

    public function test_is_mfa_optional_false_para_administrador_geral(): void
    {
        $role = Role::on('tenant')->firstOrCreate(['nome' => 'administrador_geral'], ['descricao' => 'Administrador Geral', 'is_padrao' => true, 'is_ativo' => true]);
        $user = User::factory()->create(['role_id' => $role->id]);

        $this->assertFalse($user->isMfaOptional());
    }

    public function test_is_mfa_optional_false_para_gabinete(): void
    {
        $role = Role::on('tenant')->firstOrCreate(['nome' => 'gabinete'], ['descricao' => 'Gabinete', 'is_padrao' => true, 'is_ativo' => true]);
        $user = User::factory()->create(['role_id' => $role->id]);

        $this->assertFalse($user->isMfaOptional());
    }

    public function test_is_mfa_supported_true_para_obrigatorios(): void
    {
        $role = Role::on('tenant')->firstOrCreate(['nome' => 'administrador_geral'], ['descricao' => 'Administrador Geral', 'is_padrao' => true, 'is_ativo' => true]);
        $user = User::factory()->create(['role_id' => $role->id]);

        $this->assertTrue($user->isMfaSupported());
    }

    public function test_is_mfa_supported_true_para_opcionais(): void
    {
        $role = Role::on('tenant')->firstOrCreate(['nome' => 'secretario'], ['descricao' => 'Secretario', 'is_padrao' => true, 'is_ativo' => true]);
        $user = User::factory()->create(['role_id' => $role->id]);

        $this->assertTrue($user->isMfaSupported());
    }

    public function test_is_mfa_supported_false_para_fiscal_contrato(): void
    {
        $role = Role::on('tenant')->firstOrCreate(['nome' => 'fiscal_contrato'], ['descricao' => 'Fiscal', 'is_padrao' => true, 'is_ativo' => true]);
        $user = User::factory()->create(['role_id' => $role->id]);

        $this->assertFalse($user->isMfaSupported());
    }

    public function test_is_mfa_supported_false_para_gabinete(): void
    {
        $role = Role::on('tenant')->firstOrCreate(['nome' => 'gabinete'], ['descricao' => 'Gabinete', 'is_padrao' => true, 'is_ativo' => true]);
        $user = User::factory()->create(['role_id' => $role->id]);

        $this->assertFalse($user->isMfaSupported());
    }
}
