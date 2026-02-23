<?php

namespace Tests\Feature\Scopes;

use App\Models\Alerta;
use App\Models\Contrato;
use App\Models\Scopes\SecretariaScope;
use App\Models\Secretaria;
use App\Models\User;
use Tests\TestCase;
use Tests\Traits\RunsTenantMigrations;
use Tests\Traits\SeedsTenantData;

/**
 * Testes do Eloquent Global Scope por secretaria (RN-326).
 *
 * Regras:
 * - Perfis estrategicos (administrador_geral, controladoria, gabinete) veem tudo (RN-327)
 * - Demais perfis veem apenas contratos das secretarias vinculadas
 * - Sem autenticacao: scope nao aplica (jobs, CLI)
 */
class SecretariaScopeTest extends TestCase
{
    use RunsTenantMigrations;
    use SeedsTenantData;

    protected Secretaria $secretariaA;
    protected Secretaria $secretariaB;
    protected Contrato $contratoA;
    protected Contrato $contratoB;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpTenant();
        $this->seedBaseData();

        // Criar 2 secretarias com 1 contrato cada
        $this->secretariaA = Secretaria::factory()->create(['nome' => 'Secretaria de Educacao']);
        $this->secretariaB = Secretaria::factory()->create(['nome' => 'Secretaria de Saude']);

        $this->contratoA = Contrato::withoutGlobalScope(SecretariaScope::class)
            ->create(Contrato::factory()->make([
                'secretaria_id' => $this->secretariaA->id,
            ])->toArray());

        $this->contratoB = Contrato::withoutGlobalScope(SecretariaScope::class)
            ->create(Contrato::factory()->make([
                'secretaria_id' => $this->secretariaB->id,
            ])->toArray());
    }

    // --- Bypass: sem autenticacao ---

    public function test_usuario_sem_autenticacao_ve_todos_contratos(): void
    {
        // Sem actingAs — simula job/CLI
        $contratos = Contrato::all();

        $this->assertGreaterThanOrEqual(2, $contratos->count());
        $this->assertTrue($contratos->contains('id', $this->contratoA->id));
        $this->assertTrue($contratos->contains('id', $this->contratoB->id));
    }

    // --- Bypass: perfis estrategicos (RN-327) ---

    public function test_administrador_geral_ve_todos_contratos(): void
    {
        $admin = $this->createAdminUser();
        $this->actingAs($admin);

        $contratos = Contrato::all();

        $this->assertTrue($contratos->contains('id', $this->contratoA->id));
        $this->assertTrue($contratos->contains('id', $this->contratoB->id));
    }

    public function test_controladoria_ve_todos_contratos(): void
    {
        $user = $this->createUserWithRole('controladoria');
        $this->actingAs($user);

        $contratos = Contrato::all();

        $this->assertTrue($contratos->contains('id', $this->contratoA->id));
        $this->assertTrue($contratos->contains('id', $this->contratoB->id));
    }

    public function test_gabinete_ve_todos_contratos(): void
    {
        $user = $this->createUserWithRole('gabinete');
        $this->actingAs($user);

        $contratos = Contrato::all();

        $this->assertTrue($contratos->contains('id', $this->contratoA->id));
        $this->assertTrue($contratos->contains('id', $this->contratoB->id));
    }

    // --- Filtro ativo: perfis nao-estrategicos (RN-326) ---

    public function test_secretario_ve_apenas_contratos_da_sua_secretaria(): void
    {
        $user = $this->createUserWithSecretaria('secretario', $this->secretariaA);
        $this->actingAs($user);

        $contratos = Contrato::all();

        $this->assertTrue($contratos->contains('id', $this->contratoA->id));
        $this->assertFalse($contratos->contains('id', $this->contratoB->id));
    }

    public function test_gestor_contrato_ve_apenas_contratos_da_sua_secretaria(): void
    {
        $user = $this->createUserWithSecretaria('gestor_contrato', $this->secretariaB);
        $this->actingAs($user);

        $contratos = Contrato::all();

        $this->assertFalse($contratos->contains('id', $this->contratoA->id));
        $this->assertTrue($contratos->contains('id', $this->contratoB->id));
    }

    public function test_usuario_com_multiplas_secretarias_ve_contratos_de_todas(): void
    {
        $user = $this->createUserWithSecretaria('secretario', $this->secretariaA);
        $user->secretarias()->attach($this->secretariaB->id);
        $this->actingAs($user);

        $contratos = Contrato::all();

        $this->assertTrue($contratos->contains('id', $this->contratoA->id));
        $this->assertTrue($contratos->contains('id', $this->contratoB->id));
    }

    public function test_usuario_sem_secretaria_vinculada_nao_ve_nenhum_contrato(): void
    {
        $user = $this->createUserWithRole('secretario');
        // Nao vincula nenhuma secretaria
        $this->actingAs($user);

        $contratos = Contrato::all();

        $this->assertFalse($contratos->contains('id', $this->contratoA->id));
        $this->assertFalse($contratos->contains('id', $this->contratoB->id));
    }

    // --- Route model binding ---

    public function test_scope_aplica_em_route_model_binding(): void
    {
        $user = $this->createUserWithSecretaria('secretario', $this->secretariaA, [
            'mfa_secret' => 'TESTSECRETKEY123',
            'mfa_enabled_at' => null,
        ]);

        // Contrato da secretaria do usuario — deve acessar
        $response = $this->actingAs($user)
            ->withSession(['mfa_verified' => true])
            ->get(route('tenant.contratos.show', $this->contratoA));

        $response->assertStatus(200);

        // Contrato de outra secretaria — 404 (scope filtra antes do binding)
        $response = $this->actingAs($user)
            ->withSession(['mfa_verified' => true])
            ->get(route('tenant.contratos.show', $this->contratoB));

        $response->assertStatus(404);
    }

    // --- whereHas propaga scope para Alertas ---

    public function test_scope_aplica_em_whereHas_contrato_para_alertas(): void
    {
        $alertaA = Alerta::factory()->create(['contrato_id' => $this->contratoA->id]);
        $alertaB = Alerta::factory()->create(['contrato_id' => $this->contratoB->id]);

        $user = $this->createUserWithSecretaria('secretario', $this->secretariaA);
        $this->actingAs($user);

        // whereHas('contrato') propaga o SecretariaScope na subquery
        $alertas = Alerta::whereHas('contrato')->get();

        $this->assertTrue($alertas->contains('id', $alertaA->id));
        $this->assertFalse($alertas->contains('id', $alertaB->id));
    }

    // --- withoutGlobalScope desativa filtro ---

    public function test_withoutGlobalScope_desativa_filtro(): void
    {
        $user = $this->createUserWithSecretaria('secretario', $this->secretariaA);
        $this->actingAs($user);

        // Com scope: ve apenas 1
        $comScope = Contrato::all();
        $this->assertEquals(1, $comScope->where('id', $this->contratoA->id)->count());
        $this->assertEquals(0, $comScope->where('id', $this->contratoB->id)->count());

        // Sem scope: ve todos
        $semScope = Contrato::withoutGlobalScope(SecretariaScope::class)->get();
        $this->assertTrue($semScope->contains('id', $this->contratoA->id));
        $this->assertTrue($semScope->contains('id', $this->contratoB->id));
    }
}
