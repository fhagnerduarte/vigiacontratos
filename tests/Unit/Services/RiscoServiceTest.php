<?php

namespace Tests\Unit\Services;

use App\Enums\CategoriaContrato;
use App\Enums\NivelRisco;
use App\Enums\TipoContrato;
use App\Models\Contrato;
use App\Models\Documento;
use App\Models\Fiscal;
use App\Models\User;
use App\Services\RiscoService;
use Tests\TestCase;
use Tests\Traits\RunsTenantMigrations;
use Tests\Traits\SeedsTenantData;

class RiscoServiceTest extends TestCase
{
    use RunsTenantMigrations;
    use SeedsTenantData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpTenant();
        $this->seedBaseData();
    }

    public function test_contrato_sem_riscos_retorna_baixo(): void
    {
        $contrato = Contrato::factory()->create([
            'data_fim' => now()->addYear(),
            'valor_global' => 50000,
            'modalidade_contratacao' => 'pregao_eletronico',
            'numero_processo' => '12345/2026',
            'prazo_meses' => 12,
            'percentual_executado' => 30,
        ]);

        // Designa fiscal para evitar +20pts operacional
        Fiscal::factory()->create([
            'contrato_id' => $contrato->id,
            'is_atual' => true,
        ]);

        // Adiciona todos os documentos obrigatorios para zerar risco documental
        $uploader = User::factory()->create();
        $docDefaults = [
            'documentable_type' => Contrato::class,
            'documentable_id' => $contrato->id,
            'uploaded_by' => $uploader->id,
        ];
        Documento::factory()->contratoOriginal()->create($docDefaults);
        Documento::factory()->publicacaoOficial()->create($docDefaults);
        Documento::factory()->parecerJuridico()->create($docDefaults);
        Documento::factory()->notaEmpenho()->create($docDefaults);
        Documento::factory()->create(array_merge($docDefaults, [
            'tipo_documento' => 'relatorio_fiscalizacao',
        ]));

        $contrato->load('fiscalAtual', 'documentos');
        $resultado = RiscoService::calcular($contrato);

        $this->assertEquals(NivelRisco::Baixo, $resultado['nivel']);
    }

    public function test_contrato_vencendo_em_menos_de_30_dias(): void
    {
        $contrato = Contrato::factory()->create([
            'data_fim' => now()->addDays(15),
            'valor_global' => 50000,
            'numero_processo' => '12345/2026',
        ]);

        Fiscal::factory()->create(['contrato_id' => $contrato->id, 'is_atual' => true]);
        $contrato->load('fiscalAtual', 'documentos');

        $resultado = RiscoService::calcularExpandido($contrato);

        $this->assertGreaterThanOrEqual(15, $resultado['categorias']['vencimento']['score']);
    }

    public function test_contrato_valor_acima_1_milhao(): void
    {
        $contrato = Contrato::factory()->create([
            'data_fim' => now()->addYear(),
            'valor_global' => 2000000,
            'numero_processo' => '12345/2026',
        ]);

        Fiscal::factory()->create(['contrato_id' => $contrato->id, 'is_atual' => true]);
        $contrato->load('fiscalAtual', 'documentos');

        $resultado = RiscoService::calcularExpandido($contrato);

        $this->assertGreaterThanOrEqual(10, $resultado['categorias']['financeiro']['score']);
    }

    public function test_contrato_sem_fiscal_designado(): void
    {
        $contrato = Contrato::factory()->create([
            'data_fim' => now()->addYear(),
            'numero_processo' => '12345/2026',
        ]);

        $contrato->load('fiscalAtual', 'documentos');
        $resultado = RiscoService::calcularExpandido($contrato);

        $this->assertGreaterThanOrEqual(20, $resultado['categorias']['operacional']['score']);
        $this->assertContains('Sem fiscal designado (+20pts)', $resultado['categorias']['operacional']['criterios']);
    }

    public function test_contrato_modalidade_sensivel_sem_fundamento(): void
    {
        $contrato = Contrato::factory()->create([
            'data_fim' => now()->addYear(),
            'modalidade_contratacao' => 'dispensa',
            'fundamento_legal' => null,
            'numero_processo' => '12345/2026',
        ]);

        Fiscal::factory()->create(['contrato_id' => $contrato->id, 'is_atual' => true]);
        $contrato->load('fiscalAtual', 'documentos');

        $resultado = RiscoService::calcularExpandido($contrato);

        // Sensivel sem fundamento = 15pts + sensivel base = 10pts = 25pts
        $this->assertGreaterThanOrEqual(25, $resultado['categorias']['juridico']['score']);
    }

    public function test_contrato_sem_documentos(): void
    {
        $contrato = Contrato::factory()->create([
            'data_fim' => now()->addYear(),
            'numero_processo' => '12345/2026',
        ]);

        Fiscal::factory()->create(['contrato_id' => $contrato->id, 'is_atual' => true]);
        $contrato->load('fiscalAtual', 'documentos');

        $resultado = RiscoService::calcularExpandido($contrato);

        // 4 obrigatorios * 5pts + fiscalizacao 5pts + nenhum doc 10pts = 35pts
        $this->assertGreaterThanOrEqual(35, $resultado['categorias']['documental']['score']);
    }

    public function test_contrato_essencial_vencendo_60_dias(): void
    {
        $contrato = Contrato::factory()->create([
            'data_fim' => now()->addDays(45),
            'categoria' => CategoriaContrato::Essencial,
            'numero_processo' => '12345/2026',
        ]);

        Fiscal::factory()->create(['contrato_id' => $contrato->id, 'is_atual' => true]);
        $contrato->load('fiscalAtual', 'documentos');

        $resultado = RiscoService::calcularExpandido($contrato);

        $this->assertGreaterThanOrEqual(20, $resultado['categorias']['operacional']['score']);
    }

    public function test_score_nunca_ultrapassa_100(): void
    {
        // Contrato com todos os criterios de risco possiveis
        $contrato = Contrato::factory()->create([
            'data_fim' => now()->addDays(5),
            'valor_global' => 5000000,
            'modalidade_contratacao' => 'dispensa',
            'fundamento_legal' => null,
            'numero_processo' => '',
            'percentual_executado' => 150,
            'prazo_meses' => 36,
            'categoria' => CategoriaContrato::Essencial,
            'tipo' => TipoContrato::Servico,
            'prorrogacao_automatica' => false,
        ]);

        $contrato->load('fiscalAtual', 'documentos');
        $resultado = RiscoService::calcular($contrato);

        $this->assertLessThanOrEqual(100, $resultado['score']);
    }

    public function test_nivel_alto_quando_score_60_ou_mais(): void
    {
        // Sem fiscal (+20) + sem docs (+35) + valor alto (+10) = 65pts
        $contrato = Contrato::factory()->create([
            'data_fim' => now()->addYear(),
            'valor_global' => 2000000,
            'numero_processo' => '12345/2026',
        ]);

        $contrato->load('fiscalAtual', 'documentos');
        $resultado = RiscoService::calcular($contrato);

        $this->assertEquals(NivelRisco::Alto, $resultado['nivel']);
        $this->assertGreaterThanOrEqual(60, $resultado['score']);
    }

    public function test_calcular_expandido_retorna_5_categorias(): void
    {
        $contrato = Contrato::factory()->create([
            'data_fim' => now()->addYear(),
            'numero_processo' => '12345/2026',
        ]);

        Fiscal::factory()->create(['contrato_id' => $contrato->id, 'is_atual' => true]);
        $contrato->load('fiscalAtual', 'documentos');

        $resultado = RiscoService::calcularExpandido($contrato);

        $this->assertArrayHasKey('categorias', $resultado);
        $this->assertArrayHasKey('vencimento', $resultado['categorias']);
        $this->assertArrayHasKey('financeiro', $resultado['categorias']);
        $this->assertArrayHasKey('documental', $resultado['categorias']);
        $this->assertArrayHasKey('juridico', $resultado['categorias']);
        $this->assertArrayHasKey('operacional', $resultado['categorias']);
    }

    public function test_cada_categoria_retorna_score_e_criterios(): void
    {
        $contrato = Contrato::factory()->create(['data_fim' => now()->addYear()]);
        $contrato->load('fiscalAtual', 'documentos');

        $resultado = RiscoService::calcularExpandido($contrato);

        foreach ($resultado['categorias'] as $categoria) {
            $this->assertArrayHasKey('score', $categoria);
            $this->assertArrayHasKey('criterios', $categoria);
            $this->assertIsInt($categoria['score']);
            $this->assertIsArray($categoria['criterios']);
        }
    }
}
