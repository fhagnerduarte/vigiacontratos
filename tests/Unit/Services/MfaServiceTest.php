<?php

namespace Tests\Unit\Services;

use App\Models\Role;
use App\Models\User;
use App\Services\MfaService;
use PragmaRX\Google2FA\Google2FA;
use Tests\TestCase;
use Tests\Traits\RunsTenantMigrations;

class MfaServiceTest extends TestCase
{
    use RunsTenantMigrations;

    protected MfaService $mfaService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mfaService = new MfaService();
    }

    public function test_generate_secret_retorna_string_de_16_caracteres(): void
    {
        $secret = $this->mfaService->generateSecret();

        $this->assertIsString($secret);
        $this->assertEquals(16, strlen($secret));
    }

    public function test_generate_secret_retorna_valores_unicos(): void
    {
        $secret1 = $this->mfaService->generateSecret();
        $secret2 = $this->mfaService->generateSecret();

        $this->assertNotEquals($secret1, $secret2);
    }

    public function test_verify_code_com_codigo_valido(): void
    {
        $google2fa = new Google2FA();
        $secret = $google2fa->generateSecretKey();
        $validCode = $google2fa->getCurrentOtp($secret);

        $this->assertTrue($this->mfaService->verifyCode($secret, $validCode));
    }

    public function test_verify_code_com_codigo_invalido(): void
    {
        $secret = $this->mfaService->generateSecret();

        $this->assertFalse($this->mfaService->verifyCode($secret, '000000'));
    }

    public function test_generate_recovery_codes_retorna_8_codigos(): void
    {
        $codes = $this->mfaService->generateRecoveryCodes();

        $this->assertCount(8, $codes);
    }

    public function test_generate_recovery_codes_formato_xxxx_xxxx(): void
    {
        $codes = $this->mfaService->generateRecoveryCodes();

        foreach ($codes as $code) {
            $this->assertMatchesRegularExpression('/^[A-Z0-9]{4}-[A-Z0-9]{4}$/', $code);
        }
    }

    public function test_generate_recovery_codes_quantidade_customizada(): void
    {
        $codes = $this->mfaService->generateRecoveryCodes(4);

        $this->assertCount(4, $codes);
    }

    public function test_generate_recovery_codes_sao_unicos(): void
    {
        $codes = $this->mfaService->generateRecoveryCodes();

        $this->assertCount(8, array_unique($codes));
    }

    public function test_generate_qr_code_data_uri(): void
    {
        $role = Role::on('tenant')->firstOrCreate(['nome' => 'administrador_geral'], ['descricao' => 'Administrador Geral', 'is_padrao' => true, 'is_ativo' => true]);
        $user = User::factory()->create(['role_id' => $role->id]);
        $secret = $this->mfaService->generateSecret();

        $dataUri = $this->mfaService->generateQrCodeDataUri($user, $secret);

        $this->assertStringStartsWith('data:image/svg+xml;base64,', $dataUri);
    }

    public function test_enable_mfa_com_codigo_valido(): void
    {
        $role = Role::on('tenant')->firstOrCreate(['nome' => 'administrador_geral'], ['descricao' => 'Administrador Geral', 'is_padrao' => true, 'is_ativo' => true]);
        $user = User::factory()->create(['role_id' => $role->id]);
        $google2fa = new Google2FA();
        $secret = $google2fa->generateSecretKey();
        $validCode = $google2fa->getCurrentOtp($secret);

        $result = $this->mfaService->enableMfa($user, $secret, $validCode);

        $this->assertIsArray($result);
        $this->assertCount(8, $result);

        $user->refresh();
        $this->assertNotNull($user->mfa_enabled_at);
        $this->assertEquals($secret, $user->mfa_secret);
    }

    public function test_enable_mfa_com_codigo_invalido(): void
    {
        $role = Role::on('tenant')->firstOrCreate(['nome' => 'administrador_geral'], ['descricao' => 'Administrador Geral', 'is_padrao' => true, 'is_ativo' => true]);
        $user = User::factory()->create(['role_id' => $role->id]);
        $secret = $this->mfaService->generateSecret();

        $result = $this->mfaService->enableMfa($user, $secret, '000000');

        $this->assertFalse($result);

        $user->refresh();
        $this->assertNull($user->mfa_enabled_at);
    }

    public function test_disable_mfa(): void
    {
        $role = Role::on('tenant')->firstOrCreate(['nome' => 'secretario'], ['descricao' => 'Secretario', 'is_padrao' => true, 'is_ativo' => true]);
        $google2fa = new Google2FA();
        $secret = $google2fa->generateSecretKey();
        $validCode = $google2fa->getCurrentOtp($secret);

        $user = User::factory()->create(['role_id' => $role->id]);
        $this->mfaService->enableMfa($user, $secret, $validCode);

        $user->refresh();
        $this->assertNotNull($user->mfa_enabled_at);

        $this->mfaService->disableMfa($user);

        $user->refresh();
        $this->assertNull($user->mfa_enabled_at);
        $this->assertNull($user->mfa_secret);
        $this->assertNull($user->mfa_recovery_codes);
    }

    public function test_use_recovery_code_valido(): void
    {
        $role = Role::on('tenant')->firstOrCreate(['nome' => 'administrador_geral'], ['descricao' => 'Administrador Geral', 'is_padrao' => true, 'is_ativo' => true]);
        $google2fa = new Google2FA();
        $secret = $google2fa->generateSecretKey();
        $validCode = $google2fa->getCurrentOtp($secret);

        $user = User::factory()->create(['role_id' => $role->id]);
        $recoveryCodes = $this->mfaService->enableMfa($user, $secret, $validCode);
        $user->refresh();

        $result = $this->mfaService->useRecoveryCode($user, $recoveryCodes[0]);

        $this->assertTrue($result);
        $this->assertEquals(7, $this->mfaService->getRemainingRecoveryCodes($user->refresh()));
    }

    public function test_use_recovery_code_invalido(): void
    {
        $role = Role::on('tenant')->firstOrCreate(['nome' => 'administrador_geral'], ['descricao' => 'Administrador Geral', 'is_padrao' => true, 'is_ativo' => true]);
        $google2fa = new Google2FA();
        $secret = $google2fa->generateSecretKey();
        $validCode = $google2fa->getCurrentOtp($secret);

        $user = User::factory()->create(['role_id' => $role->id]);
        $this->mfaService->enableMfa($user, $secret, $validCode);
        $user->refresh();

        $result = $this->mfaService->useRecoveryCode($user, 'XXXX-YYYY');

        $this->assertFalse($result);
        $this->assertEquals(8, $this->mfaService->getRemainingRecoveryCodes($user->refresh()));
    }

    public function test_use_recovery_code_nao_pode_reusar(): void
    {
        $role = Role::on('tenant')->firstOrCreate(['nome' => 'administrador_geral'], ['descricao' => 'Administrador Geral', 'is_padrao' => true, 'is_ativo' => true]);
        $google2fa = new Google2FA();
        $secret = $google2fa->generateSecretKey();
        $validCode = $google2fa->getCurrentOtp($secret);

        $user = User::factory()->create(['role_id' => $role->id]);
        $recoveryCodes = $this->mfaService->enableMfa($user, $secret, $validCode);
        $user->refresh();

        $this->assertTrue($this->mfaService->useRecoveryCode($user, $recoveryCodes[0]));
        $user->refresh();

        $this->assertFalse($this->mfaService->useRecoveryCode($user, $recoveryCodes[0]));
    }

    public function test_get_remaining_recovery_codes(): void
    {
        $role = Role::on('tenant')->firstOrCreate(['nome' => 'administrador_geral'], ['descricao' => 'Administrador Geral', 'is_padrao' => true, 'is_ativo' => true]);
        $google2fa = new Google2FA();
        $secret = $google2fa->generateSecretKey();
        $validCode = $google2fa->getCurrentOtp($secret);

        $user = User::factory()->create(['role_id' => $role->id]);
        $this->mfaService->enableMfa($user, $secret, $validCode);
        $user->refresh();

        $this->assertEquals(8, $this->mfaService->getRemainingRecoveryCodes($user));
    }

    public function test_get_remaining_recovery_codes_sem_mfa(): void
    {
        $role = Role::on('tenant')->firstOrCreate(['nome' => 'administrador_geral'], ['descricao' => 'Administrador Geral', 'is_padrao' => true, 'is_ativo' => true]);
        $user = User::factory()->create(['role_id' => $role->id]);

        $this->assertEquals(0, $this->mfaService->getRemainingRecoveryCodes($user));
    }
}
