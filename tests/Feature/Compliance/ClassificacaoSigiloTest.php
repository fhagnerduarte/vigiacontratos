<?php

namespace Tests\Feature\Compliance;

use App\Enums\ClassificacaoSigilo;
use App\Models\Contrato;
use App\Models\Documento;
use App\Models\HistoricoAlteracao;
use App\Models\User;
use App\Services\ClassificacaoService;
use Tests\TestCase;
use Tests\Traits\RunsTenantMigrations;
use Tests\Traits\SeedsTenantData;

class ClassificacaoSigiloTest extends TestCase
{
    use RunsTenantMigrations, SeedsTenantData;

    protected User $admin;

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
    }

    // --- Enum ClassificacaoSigilo ---

    public function test_enum_tem_4_cases(): void
    {
        $this->assertCount(4, ClassificacaoSigilo::cases());
    }

    public function test_enum_labels(): void
    {
        $this->assertEquals('Publico', ClassificacaoSigilo::Publico->label());
        $this->assertEquals('Reservado', ClassificacaoSigilo::Reservado->label());
        $this->assertEquals('Secreto', ClassificacaoSigilo::Secreto->label());
        $this->assertEquals('Ultrassecreto', ClassificacaoSigilo::Ultrassecreto->label());
    }

    public function test_enum_prazo_anos(): void
    {
        $this->assertEquals(0, ClassificacaoSigilo::Publico->prazoAnos());
        $this->assertEquals(5, ClassificacaoSigilo::Reservado->prazoAnos());
        $this->assertEquals(15, ClassificacaoSigilo::Secreto->prazoAnos());
        $this->assertEquals(25, ClassificacaoSigilo::Ultrassecreto->prazoAnos());
    }

    public function test_enum_icone(): void
    {
        foreach (ClassificacaoSigilo::cases() as $case) {
            $this->assertNotEmpty($case->icone());
        }
    }

    public function test_enum_cor(): void
    {
        $this->assertEquals('success', ClassificacaoSigilo::Publico->cor());
        $this->assertEquals('warning', ClassificacaoSigilo::Reservado->cor());
        $this->assertEquals('danger', ClassificacaoSigilo::Secreto->cor());
        $this->assertEquals('dark', ClassificacaoSigilo::Ultrassecreto->cor());
    }

    public function test_enum_requer_justificativa(): void
    {
        $this->assertFalse(ClassificacaoSigilo::Publico->requerJustificativa());
        $this->assertTrue(ClassificacaoSigilo::Reservado->requerJustificativa());
        $this->assertTrue(ClassificacaoSigilo::Secreto->requerJustificativa());
        $this->assertTrue(ClassificacaoSigilo::Ultrassecreto->requerJustificativa());
    }

    // --- Model Contrato: campos LAI ---

    public function test_contrato_default_classificacao_publico(): void
    {
        $contrato = Contrato::factory()->create();
        $contrato->refresh();

        $this->assertEquals(ClassificacaoSigilo::Publico, $contrato->classificacao_sigilo);
        $this->assertFalse($contrato->publicado_portal);
    }

    public function test_contrato_fillable_campos_lai(): void
    {
        $contrato = Contrato::factory()->create([
            'classificacao_sigilo' => ClassificacaoSigilo::Reservado->value,
            'classificado_por' => $this->admin->id,
            'data_classificacao' => '2026-01-15',
            'justificativa_sigilo' => 'Contrato com dados estrategicos',
            'publicado_portal' => true,
        ]);

        $this->assertEquals(ClassificacaoSigilo::Reservado, $contrato->classificacao_sigilo);
        $this->assertEquals($this->admin->id, $contrato->classificado_por);
        $this->assertEquals('2026-01-15', $contrato->data_classificacao->format('Y-m-d'));
        $this->assertEquals('Contrato com dados estrategicos', $contrato->justificativa_sigilo);
        $this->assertTrue($contrato->publicado_portal);
    }

    public function test_contrato_scope_publicos(): void
    {
        $ano = '1990';
        Contrato::factory()->create(['classificacao_sigilo' => 'publico', 'ano' => $ano]);
        Contrato::factory()->create(['classificacao_sigilo' => 'reservado', 'ano' => $ano]);
        Contrato::factory()->create(['classificacao_sigilo' => 'secreto', 'ano' => $ano]);

        $publicos = Contrato::withoutGlobalScopes()->where('ano', $ano)->publicos()->count();
        $this->assertEquals(1, $publicos);
    }

    public function test_contrato_scope_visivel_no_portal(): void
    {
        Contrato::factory()->create(['classificacao_sigilo' => 'publico', 'publicado_portal' => true]);
        Contrato::factory()->create(['classificacao_sigilo' => 'publico', 'publicado_portal' => false]);
        Contrato::factory()->create(['classificacao_sigilo' => 'reservado', 'publicado_portal' => true]);

        $visiveis = Contrato::withoutGlobalScopes()->visivelNoPortal()->count();
        $this->assertEquals(1, $visiveis);
    }

    public function test_contrato_accessor_visivel_no_portal(): void
    {
        $contrato1 = Contrato::factory()->create([
            'classificacao_sigilo' => 'publico',
            'publicado_portal' => true,
        ]);
        $this->assertTrue($contrato1->visivel_no_portal);

        $contrato2 = Contrato::factory()->create([
            'classificacao_sigilo' => 'publico',
            'publicado_portal' => false,
        ]);
        $this->assertFalse($contrato2->visivel_no_portal);

        $contrato3 = Contrato::factory()->create([
            'classificacao_sigilo' => 'reservado',
            'publicado_portal' => true,
        ]);
        $this->assertFalse($contrato3->visivel_no_portal);
    }

    public function test_contrato_classificador_relationship(): void
    {
        $contrato = Contrato::factory()->create([
            'classificado_por' => $this->admin->id,
        ]);

        $this->assertNotNull($contrato->classificador);
        $this->assertEquals($this->admin->id, $contrato->classificador->id);
    }

    // --- Model Documento: campos LAI ---

    public function test_documento_default_classificacao_publico(): void
    {
        $contrato = Contrato::factory()->create();
        $documento = Documento::factory()->create([
            'documentable_type' => Contrato::class,
            'documentable_id' => $contrato->id,
        ]);
        $documento->refresh();

        $this->assertEquals(ClassificacaoSigilo::Publico, $documento->classificacao_sigilo);
        $this->assertNull($documento->justificativa_sigilo);
    }

    public function test_documento_fillable_classificacao(): void
    {
        $contrato = Contrato::factory()->create();
        $documento = Documento::factory()->create([
            'documentable_type' => Contrato::class,
            'documentable_id' => $contrato->id,
            'classificacao_sigilo' => ClassificacaoSigilo::Secreto->value,
            'justificativa_sigilo' => 'Documento com informacoes sensíveis',
        ]);

        $this->assertEquals(ClassificacaoSigilo::Secreto, $documento->classificacao_sigilo);
        $this->assertEquals('Documento com informacoes sensíveis', $documento->justificativa_sigilo);
    }

    // --- ClassificacaoService ---

    public function test_classificar_contrato_com_justificativa(): void
    {
        $contrato = Contrato::factory()->create();

        ClassificacaoService::classificar(
            $contrato,
            ClassificacaoSigilo::Reservado,
            'Contrato envolve seguranca publica',
            $this->admin,
            '127.0.0.1'
        );

        $contrato->refresh();

        $this->assertEquals(ClassificacaoSigilo::Reservado, $contrato->classificacao_sigilo);
        $this->assertEquals($this->admin->id, $contrato->classificado_por);
        $this->assertNotNull($contrato->data_classificacao);
        $this->assertEquals('Contrato envolve seguranca publica', $contrato->justificativa_sigilo);
    }

    public function test_classificar_contrato_sem_justificativa_falha(): void
    {
        $contrato = Contrato::factory()->create();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Justificativa e obrigatoria');

        ClassificacaoService::classificar(
            $contrato,
            ClassificacaoSigilo::Secreto,
            null,
            $this->admin,
            '127.0.0.1'
        );
    }

    public function test_classificar_como_publico_sem_justificativa_ok(): void
    {
        $contrato = Contrato::factory()->create([
            'classificacao_sigilo' => 'reservado',
            'justificativa_sigilo' => 'Motivo anterior',
        ]);

        ClassificacaoService::classificar(
            $contrato,
            ClassificacaoSigilo::Publico,
            null,
            $this->admin,
            '127.0.0.1'
        );

        $contrato->refresh();

        $this->assertEquals(ClassificacaoSigilo::Publico, $contrato->classificacao_sigilo);
        $this->assertNull($contrato->justificativa_sigilo);
    }

    public function test_classificar_gera_auditoria(): void
    {
        $contrato = Contrato::factory()->create([
            'classificacao_sigilo' => 'publico',
        ]);

        ClassificacaoService::classificar(
            $contrato,
            ClassificacaoSigilo::Ultrassecreto,
            'Seguranca nacional',
            $this->admin,
            '192.168.1.1'
        );

        $auditoria = HistoricoAlteracao::where('auditable_type', Contrato::class)
            ->where('auditable_id', $contrato->id)
            ->where('campo_alterado', 'classificacao_sigilo')
            ->first();

        $this->assertNotNull($auditoria);
        $this->assertEquals('publico', $auditoria->valor_anterior);
        $this->assertEquals('ultrassecreto', $auditoria->valor_novo);
        $this->assertEquals($this->admin->id, $auditoria->user_id);
        $this->assertEquals('192.168.1.1', $auditoria->ip_address);
    }

    public function test_desclassificar_contrato(): void
    {
        $contrato = Contrato::factory()->create([
            'classificacao_sigilo' => 'secreto',
            'justificativa_sigilo' => 'Motivo original',
            'classificado_por' => $this->admin->id,
            'data_classificacao' => '2026-01-01',
        ]);

        ClassificacaoService::desclassificar(
            $contrato,
            $this->admin,
            '127.0.0.1'
        );

        $contrato->refresh();

        $this->assertEquals(ClassificacaoSigilo::Publico, $contrato->classificacao_sigilo);
        $this->assertNull($contrato->justificativa_sigilo);
    }

    public function test_desclassificacao_automatica_por_prazo(): void
    {
        // Contrato reservado classificado ha 6 anos (prazo de 5 anos expirado)
        Contrato::factory()->create([
            'classificacao_sigilo' => 'reservado',
            'data_classificacao' => now()->subYears(6),
            'justificativa_sigilo' => 'Motivo antigo',
        ]);

        // Contrato secreto classificado ha 2 anos (prazo de 15 anos NAO expirado)
        Contrato::factory()->create([
            'classificacao_sigilo' => 'secreto',
            'data_classificacao' => now()->subYears(2),
            'justificativa_sigilo' => 'Motivo recente',
        ]);

        $desclassificados = ClassificacaoService::verificarDesclassificacaoAutomatica();

        $this->assertEquals(1, $desclassificados);
    }

    public function test_desclassificacao_automatica_ultrassecreto_25_anos(): void
    {
        Contrato::factory()->create([
            'classificacao_sigilo' => 'ultrassecreto',
            'data_classificacao' => now()->subYears(26),
            'justificativa_sigilo' => 'Seguranca maxima',
        ]);

        $desclassificados = ClassificacaoService::verificarDesclassificacaoAutomatica();

        $this->assertEquals(1, $desclassificados);
    }

    public function test_desclassificacao_automatica_nao_altera_dentro_prazo(): void
    {
        Contrato::factory()->create([
            'classificacao_sigilo' => 'reservado',
            'data_classificacao' => now()->subYears(3),
            'justificativa_sigilo' => 'Dentro do prazo',
        ]);

        $desclassificados = ClassificacaoService::verificarDesclassificacaoAutomatica();

        $this->assertEquals(0, $desclassificados);
    }

    // --- PermissionSeeder ---

    public function test_permissoes_classificacao_existem(): void
    {
        $permissoes = [
            'classificacao.visualizar',
            'classificacao.classificar',
            'classificacao.desclassificar',
            'classificacao.justificar',
        ];

        foreach ($permissoes as $nome) {
            $exists = \Illuminate\Support\Facades\DB::connection('tenant')
                ->table('permissions')
                ->where('nome', $nome)
                ->where('grupo', 'classificacao')
                ->exists();
            $this->assertTrue($exists, "Permissao {$nome} nao encontrada");
        }
    }

    // --- Command ---

    public function test_command_verificar_desclassificacao_existe(): void
    {
        $this->artisan('lai:verificar-desclassificacao')
            ->assertSuccessful();
    }
}
