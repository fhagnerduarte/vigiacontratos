<?php

namespace Tests\Feature\Auth;

use App\Models\LoginLog;
use App\Models\User;
use Tests\TestCase;
use Tests\Traits\RunsTenantMigrations;
use Tests\Traits\SeedsTenantData;

class LoginIsAtivoTest extends TestCase
{
    use RunsTenantMigrations;
    use SeedsTenantData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpTenant();
        $this->seedBaseData();
    }

    public function test_usuario_ativo_loga_normalmente(): void
    {
        $user = $this->createAdminUser([
            'is_ativo' => true,
            'password' => \Illuminate\Support\Facades\Hash::make('SenhaForte@123'),
        ]);

        $response = $this->post(route('tenant.login'), [
            'email' => $user->email,
            'password' => 'SenhaForte@123',
        ]);

        $response->assertRedirect();
        $this->assertAuthenticatedAs($user);
    }

    public function test_usuario_inativo_nao_consegue_logar(): void
    {
        $user = $this->createAdminUser([
            'is_ativo' => false,
            'password' => \Illuminate\Support\Facades\Hash::make('SenhaForte@123'),
        ]);

        $response = $this->post(route('tenant.login'), [
            'email' => $user->email,
            'password' => 'SenhaForte@123',
        ]);

        $this->assertGuest();
    }

    public function test_usuario_inativo_recebe_mensagem_de_erro(): void
    {
        $user = $this->createAdminUser([
            'is_ativo' => false,
            'password' => \Illuminate\Support\Facades\Hash::make('SenhaForte@123'),
        ]);

        $response = $this->post(route('tenant.login'), [
            'email' => $user->email,
            'password' => 'SenhaForte@123',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_login_de_usuario_inativo_registra_log_falho(): void
    {
        $user = $this->createAdminUser([
            'is_ativo' => false,
            'password' => \Illuminate\Support\Facades\Hash::make('SenhaForte@123'),
        ]);

        $this->post(route('tenant.login'), [
            'email' => $user->email,
            'password' => 'SenhaForte@123',
        ]);

        $log = LoginLog::where('user_id', $user->id)->latest('id')->first();
        $this->assertNotNull($log);
        $this->assertFalse($log->success);
    }
}
