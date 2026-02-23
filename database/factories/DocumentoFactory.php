<?php

namespace Database\Factories;

use App\Enums\TipoDocumentoContratual;
use App\Models\Contrato;
use App\Models\Documento;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Documento>
 */
class DocumentoFactory extends Factory
{
    protected $model = Documento::class;

    public function definition(): array
    {
        $tipo = fake()->randomElement(TipoDocumentoContratual::cases());

        return [
            'documentable_type' => Contrato::class,
            'documentable_id' => Contrato::factory(),
            'tipo_documento' => $tipo,
            'nome_original' => 'documento_' . fake()->word() . '.pdf',
            'nome_arquivo' => 'contrato_001_2026_' . $tipo->value . '_v1.pdf',
            'descricao' => null,
            'caminho' => 'documentos/testing/' . fake()->uuid() . '.pdf',
            'tamanho' => fake()->numberBetween(10000, 5000000),
            'mime_type' => 'application/pdf',
            'hash_integridade' => hash('sha256', fake()->text()),
            'versao' => 1,
            'is_versao_atual' => true,
            'uploaded_by' => User::factory(),
        ];
    }

    public function contratoOriginal(): static
    {
        return $this->state(fn () => [
            'tipo_documento' => TipoDocumentoContratual::ContratoOriginal,
        ]);
    }

    public function publicacaoOficial(): static
    {
        return $this->state(fn () => [
            'tipo_documento' => TipoDocumentoContratual::PublicacaoOficial,
        ]);
    }

    public function parecerJuridico(): static
    {
        return $this->state(fn () => [
            'tipo_documento' => TipoDocumentoContratual::ParecerJuridico,
        ]);
    }

    public function notaEmpenho(): static
    {
        return $this->state(fn () => [
            'tipo_documento' => TipoDocumentoContratual::NotaEmpenho,
        ]);
    }

    public function versaoAntiga(): static
    {
        return $this->state(fn () => [
            'is_versao_atual' => false,
        ]);
    }
}
