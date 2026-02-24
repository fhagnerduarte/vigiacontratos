<?php

namespace Tests\Feature\Policies;

use App\Models\Aditivo;
use App\Models\Contrato;
use App\Models\Secretaria;
use App\Models\User;
use App\Policies\AditivoPolicy;
use Tests\TestCase;
use Tests\Traits\RunsTenantMigrations;
use Tests\Traits\SeedsTenantData;

class AditivoPolicyTest extends TestCase
{
    use RunsTenantMigrations;
    use SeedsTenantData;

    protected User $admin;
    protected AditivoPolicy $policy;

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
        $this->policy = new AditivoPolicy();
    }

    // --- ADMIN GERAL (bypass total via before) ---

    public function test_admin_geral_pode_visualizar_aditivo_qualquer_secretaria(): void
    {
        $secretaria = Secretaria::factory()->create();
        $contrato = Contrato::factory()->create(['secretaria_id' => $secretaria->id]);
        $aditivo = Aditivo::factory()->create(['contrato_id' => $contrato->id]);

        $this->assertTrue($this->policy->view($this->admin, $aditivo));
    }

    public function test_admin_geral_pode_aprovar_aditivo_qualquer_secretaria(): void
    {
        $secretaria = Secretaria::factory()->create();
        $contrato = Contrato::factory()->create(['secretaria_id' => $secretaria->id]);
        $aditivo = Aditivo::factory()->create(['contrato_id' => $contrato->id]);

        $this->assertTrue($this->policy->aprovar($this->admin, $aditivo));
    }

    // --- CONTROLADORIA (perfil estrategico — bypass de secretaria) ---

    public function test_controladoria_pode_aprovar_aditivo_qualquer_secretaria(): void
    {
        $secretariaA = Secretaria::factory()->create();
        $secretariaB = Secretaria::factory()->create();

        $user = $this->createUserWithSecretaria('controladoria', $secretariaA);

        $contrato = Contrato::factory()->create(['secretaria_id' => $secretariaB->id]);
        $aditivo = Aditivo::factory()->create(['contrato_id' => $contrato->id]);

        $this->assertTrue($this->policy->aprovar($user, $aditivo));
    }

    // --- GESTOR CONTRATO (restrito por secretaria) ---

    public function test_gestor_pode_visualizar_aditivo_da_sua_secretaria(): void
    {
        $secretaria = Secretaria::factory()->create();
        $user = $this->createUserWithSecretaria('gestor_contrato', $secretaria);

        $contrato = Contrato::factory()->create(['secretaria_id' => $secretaria->id]);
        $aditivo = Aditivo::factory()->create(['contrato_id' => $contrato->id]);

        $this->assertTrue($this->policy->view($user, $aditivo));
    }

    public function test_gestor_nao_pode_visualizar_aditivo_de_outra_secretaria(): void
    {
        $secretariaDoUsuario = Secretaria::factory()->create();
        $outraSecretaria = Secretaria::factory()->create();

        $user = $this->createUserWithSecretaria('gestor_contrato', $secretariaDoUsuario);

        $contrato = Contrato::factory()->create(['secretaria_id' => $outraSecretaria->id]);
        $aditivo = Aditivo::factory()->create(['contrato_id' => $contrato->id]);

        $this->assertFalse($this->policy->view($user, $aditivo));
    }

    public function test_gestor_pode_criar_aditivo(): void
    {
        $secretaria = Secretaria::factory()->create();
        $user = $this->createUserWithSecretaria('gestor_contrato', $secretaria);

        $this->assertTrue($this->policy->create($user));
    }

    public function test_gestor_nao_pode_aprovar_aditivo(): void
    {
        $secretaria = Secretaria::factory()->create();
        $user = $this->createUserWithSecretaria('gestor_contrato', $secretaria);

        $contrato = Contrato::factory()->create(['secretaria_id' => $secretaria->id]);
        $aditivo = Aditivo::factory()->create(['contrato_id' => $contrato->id]);

        // gestor_contrato nao tem aditivo.aprovar
        $this->assertFalse($this->policy->aprovar($user, $aditivo));
    }

    // --- FISCAL CONTRATO (permissoes limitadas) ---

    public function test_fiscal_nao_pode_criar_aditivo(): void
    {
        $secretaria = Secretaria::factory()->create();
        $user = $this->createUserWithSecretaria('fiscal_contrato', $secretaria);

        // fiscal_contrato nao tem aditivo.criar
        $this->assertFalse($this->policy->create($user));
    }

    public function test_fiscal_nao_pode_aprovar_aditivo(): void
    {
        $secretaria = Secretaria::factory()->create();
        $user = $this->createUserWithSecretaria('fiscal_contrato', $secretaria);

        $contrato = Contrato::factory()->create(['secretaria_id' => $secretaria->id]);
        $aditivo = Aditivo::factory()->create(['contrato_id' => $contrato->id]);

        // fiscal_contrato nao tem aditivo.aprovar
        $this->assertFalse($this->policy->aprovar($user, $aditivo));
    }

    // --- SECRETARIO (restrito por secretaria) ---

    public function test_secretario_pode_aprovar_aditivo_da_sua_secretaria(): void
    {
        $secretaria = Secretaria::factory()->create();
        $user = $this->createUserWithSecretaria('secretario', $secretaria);

        $contrato = Contrato::factory()->create(['secretaria_id' => $secretaria->id]);
        $aditivo = Aditivo::factory()->create(['contrato_id' => $contrato->id]);

        $this->assertTrue($this->policy->aprovar($user, $aditivo));
    }

    public function test_secretario_nao_pode_aprovar_aditivo_de_outra_secretaria(): void
    {
        $secretariaDoUsuario = Secretaria::factory()->create();
        $outraSecretaria = Secretaria::factory()->create();

        $user = $this->createUserWithSecretaria('secretario', $secretariaDoUsuario);

        $contrato = Contrato::factory()->create(['secretaria_id' => $outraSecretaria->id]);
        $aditivo = Aditivo::factory()->create(['contrato_id' => $contrato->id]);

        $this->assertFalse($this->policy->aprovar($user, $aditivo));
    }

    // --- PROCURADORIA (tem aditivo.aprovar, restrito por secretaria) ---

    public function test_procuradoria_pode_aprovar_aditivo_da_sua_secretaria(): void
    {
        $secretaria = Secretaria::factory()->create();
        $user = $this->createUserWithSecretaria('procuradoria', $secretaria);

        $contrato = Contrato::factory()->create(['secretaria_id' => $secretaria->id]);
        $aditivo = Aditivo::factory()->create(['contrato_id' => $contrato->id]);

        $this->assertTrue($this->policy->aprovar($user, $aditivo));
    }

    public function test_procuradoria_nao_pode_aprovar_aditivo_de_outra_secretaria(): void
    {
        $secretariaDoUsuario = Secretaria::factory()->create();
        $outraSecretaria = Secretaria::factory()->create();

        $user = $this->createUserWithSecretaria('procuradoria', $secretariaDoUsuario);

        $contrato = Contrato::factory()->create(['secretaria_id' => $outraSecretaria->id]);
        $aditivo = Aditivo::factory()->create(['contrato_id' => $contrato->id]);

        // procuradoria nao e perfil estrategico — restrito a sua secretaria
        $this->assertFalse($this->policy->aprovar($user, $aditivo));
    }
}
