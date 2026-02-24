<?php

namespace Tests\Feature\Controllers;

use App\Models\Role;
use App\Models\User;
use App\Services\MfaService;
use Illuminate\Support\Facades\Hash;
use Mockery;
use Tests\TestCase;
use Tests\Traits\RunsTenantMigrations;
use Tests\Traits\SeedsTenantData;

class MfaControllerTest extends TestCase
{
    use RunsTenantMigrations;
    use SeedsTenantData;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpTenant();
        $this->seedBaseData();

        // Habilitar MFA no tenant
        $this->tenant->update([
            'mfa_habilitado' => true,
            'mfa_modo' => 'opcional',
        ]);

        // Criar usuario com role que suporta MFA (secretario = opcional)
        $role = Role::where('nome', 'secretario')->first()
            ?? Role::factory()->create(['nome' => 'secretario']);

        $this->user = User::factory()->create([
            'role_id' => $role->id,
            'is_ativo' => true,
        ]);
    }

    // ─── SETUP ──────────────────────────────────────────────

    public function test_setup_renderiza_pagina_com_qr_code(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('tenant.mfa.setup'));

        $response->assertStatus(200);
        $response->assertViewIs('auth.mfa.setup');
        $response->assertViewHas('qrCodeDataUri');
        $response->assertViewHas('secret');
    }

    public function test_setup_redirect_se_mfa_ja_ativado(): void
    {
        $this->user->update([
            'mfa_secret' => 'TESTSECRETKEY123',
            'mfa_enabled_at' => now(),
            'mfa_recovery_codes' => encrypt(json_encode(['AAAA-BBBB'])),
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('tenant.mfa.setup'));

        $response->assertRedirect(route('tenant.dashboard'));
    }

    public function test_setup_redirect_se_perfil_nao_suporta_mfa(): void
    {
        $role = Role::where('nome', 'fiscal_contrato')->first()
            ?? Role::factory()->create(['nome' => 'fiscal_contrato']);
        $user = User::factory()->create([
            'role_id' => $role->id,
            'is_ativo' => true,
        ]);

        // Tenant com MFA desabilitado — nenhum perfil suporta
        $this->tenant->update([
            'mfa_habilitado' => false,
        ]);

        $response = $this->actingAs($user)
            ->get(route('tenant.mfa.setup'));

        $response->assertRedirect(route('tenant.dashboard'));
    }

    public function test_setup_redirect_se_tenant_nao_habilita_mfa(): void
    {
        $this->tenant->update(['mfa_habilitado' => false]);

        $response = $this->actingAs($this->user)
            ->get(route('tenant.mfa.setup'));

        $response->assertRedirect(route('tenant.dashboard'));
    }

    // ─── ENABLE ─────────────────────────────────────────────

    public function test_enable_com_codigo_valido_ativa_mfa(): void
    {
        $mockService = Mockery::mock(MfaService::class);
        $mockService->shouldReceive('generateSecret')->andReturn('FAKESECRETKEY123');
        $mockService->shouldReceive('generateQrCodeDataUri')->andReturn('data:image/svg+xml;base64,fake');
        $mockService->shouldReceive('enableMfa')->andReturn(['CODE-0001', 'CODE-0002']);
        $this->app->instance(MfaService::class, $mockService);

        $response = $this->actingAs($this->user)
            ->withSession(['mfa_setup_secret' => 'FAKESECRETKEY123'])
            ->post(route('tenant.mfa.enable'), ['code' => '123456']);

        $response->assertStatus(200);
        $response->assertViewIs('auth.mfa.recovery-codes');
        $response->assertViewHas('recoveryCodes');
    }

    public function test_enable_com_codigo_invalido_redireciona_com_erro(): void
    {
        $mockService = Mockery::mock(MfaService::class);
        $mockService->shouldReceive('enableMfa')->andReturn(false);
        $this->app->instance(MfaService::class, $mockService);

        $response = $this->actingAs($this->user)
            ->withSession(['mfa_setup_secret' => 'FAKESECRETKEY123'])
            ->post(route('tenant.mfa.enable'), ['code' => '000000']);

        $response->assertRedirect(route('tenant.mfa.setup'));
        $response->assertSessionHas('error');
    }

    public function test_enable_sem_secret_na_sessao_redireciona(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('tenant.mfa.enable'), ['code' => '123456']);

        $response->assertRedirect(route('tenant.mfa.setup'));
        $response->assertSessionHas('error');
    }

    // ─── SHOW VERIFY ────────────────────────────────────────

    public function test_show_verify_renderiza_form_se_mfa_ativado(): void
    {
        $this->user->update([
            'mfa_secret' => 'TESTSECRETKEY123',
            'mfa_enabled_at' => now(),
            'mfa_recovery_codes' => encrypt(json_encode(['AAAA-BBBB'])),
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('tenant.mfa.verify'));

        $response->assertStatus(200);
        $response->assertViewIs('auth.mfa.verify');
    }

    public function test_show_verify_redirect_se_ja_verificado(): void
    {
        $this->user->update([
            'mfa_secret' => 'TESTSECRETKEY123',
            'mfa_enabled_at' => now(),
            'mfa_recovery_codes' => encrypt(json_encode(['AAAA-BBBB'])),
        ]);

        $response = $this->actingAs($this->user)
            ->withSession(['mfa_verified' => true])
            ->get(route('tenant.mfa.verify'));

        $response->assertRedirect(route('tenant.dashboard'));
    }

    // ─── VERIFY ─────────────────────────────────────────────

    public function test_verify_com_codigo_valido_marca_sessao(): void
    {
        $this->user->update([
            'mfa_secret' => 'TESTSECRETKEY123',
            'mfa_enabled_at' => now(),
            'mfa_recovery_codes' => encrypt(json_encode(['AAAA-BBBB'])),
        ]);

        $mockService = Mockery::mock(MfaService::class);
        $mockService->shouldReceive('verifyCode')->andReturn(true);
        $this->app->instance(MfaService::class, $mockService);

        $response = $this->actingAs($this->user)
            ->post(route('tenant.mfa.verify.submit'), ['code' => '123456']);

        $response->assertRedirect(route('tenant.dashboard'));
    }

    public function test_verify_com_codigo_invalido_retorna_erro(): void
    {
        $this->user->update([
            'mfa_secret' => 'TESTSECRETKEY123',
            'mfa_enabled_at' => now(),
            'mfa_recovery_codes' => encrypt(json_encode(['AAAA-BBBB'])),
        ]);

        $mockService = Mockery::mock(MfaService::class);
        $mockService->shouldReceive('verifyCode')->andReturn(false);
        $this->app->instance(MfaService::class, $mockService);

        $response = $this->actingAs($this->user)
            ->post(route('tenant.mfa.verify.submit'), ['code' => '000000']);

        $response->assertSessionHas('error');
    }

    // ─── DISABLE ────────────────────────────────────────────

    public function test_disable_com_senha_e_codigo_validos(): void
    {
        $this->user->update([
            'mfa_secret' => 'TESTSECRETKEY123',
            'mfa_enabled_at' => now(),
            'mfa_recovery_codes' => encrypt(json_encode(['AAAA-BBBB'])),
            'password' => Hash::make('password123'),
        ]);

        $mockService = Mockery::mock(MfaService::class);
        $mockService->shouldReceive('verifyCode')->andReturn(true);
        $mockService->shouldReceive('disableMfa')->once();
        $this->app->instance(MfaService::class, $mockService);

        $response = $this->actingAs($this->user)
            ->withSession(['mfa_verified' => true])
            ->post(route('tenant.mfa.disable'), [
                'password' => 'password123',
                'code' => '123456',
            ]);

        $response->assertRedirect(route('tenant.dashboard'));
        $response->assertSessionHas('success');
    }

    public function test_disable_bloqueado_se_mfa_obrigatorio(): void
    {
        // Configurar tenant para MFA obrigatorio global
        $this->tenant->update([
            'mfa_habilitado' => true,
            'mfa_modo' => 'obrigatorio',
        ]);

        $adminRole = Role::where('nome', 'administrador_geral')->first();
        $admin = User::factory()->create([
            'role_id' => $adminRole->id,
            'is_ativo' => true,
            'mfa_secret' => 'TESTSECRETKEY123',
            'mfa_enabled_at' => now(),
            'mfa_recovery_codes' => encrypt(json_encode(['AAAA-BBBB'])),
            'password' => Hash::make('password123'),
        ]);

        $response = $this->actingAs($admin)
            ->withSession(['mfa_verified' => true])
            ->post(route('tenant.mfa.disable'), [
                'password' => 'password123',
                'code' => '123456',
            ]);

        $response->assertSessionHas('error');
    }

    // ─── RECOVERY ───────────────────────────────────────────

    public function test_use_recovery_code_valido_autentica(): void
    {
        $this->user->update([
            'mfa_secret' => 'TESTSECRETKEY123',
            'mfa_enabled_at' => now(),
            'mfa_recovery_codes' => encrypt(json_encode(['AAAA-BBBB', 'CCCC-DDDD'])),
        ]);

        $mockService = Mockery::mock(MfaService::class);
        $mockService->shouldReceive('useRecoveryCode')->andReturn(true);
        $mockService->shouldReceive('getRemainingRecoveryCodes')->andReturn(1);
        $this->app->instance(MfaService::class, $mockService);

        $response = $this->actingAs($this->user)
            ->post(route('tenant.mfa.recovery.submit'), [
                'recovery_code' => 'AAAA-BBBB',
            ]);

        $response->assertRedirect(route('tenant.dashboard'));
    }

    public function test_use_recovery_code_invalido_retorna_erro(): void
    {
        $this->user->update([
            'mfa_secret' => 'TESTSECRETKEY123',
            'mfa_enabled_at' => now(),
            'mfa_recovery_codes' => encrypt(json_encode(['AAAA-BBBB'])),
        ]);

        $mockService = Mockery::mock(MfaService::class);
        $mockService->shouldReceive('useRecoveryCode')->andReturn(false);
        $this->app->instance(MfaService::class, $mockService);

        $response = $this->actingAs($this->user)
            ->post(route('tenant.mfa.recovery.submit'), [
                'recovery_code' => 'XXXX-YYYY',
            ]);

        $response->assertSessionHas('error');
    }
}
