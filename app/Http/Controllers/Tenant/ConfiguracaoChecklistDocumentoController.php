<?php

namespace App\Http\Controllers\Tenant;

use App\Enums\FaseContratual;
use App\Enums\TipoContrato;
use App\Enums\TipoDocumentoContratual;
use App\Http\Controllers\Controller;
use App\Models\ConfiguracaoChecklistDocumento;
use App\Services\ChecklistService;
use App\Services\DocumentoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ConfiguracaoChecklistDocumentoController extends Controller
{
    /**
     * Exibe matriz de configuracao do checklist organizada por fase contratual.
     */
    public function index(): View
    {
        $tiposContrato = TipoContrato::cases();
        $tiposDocumento = TipoDocumentoContratual::cases();
        $fases = FaseContratual::cases();

        $configuracoes = ConfiguracaoChecklistDocumento::all()
            ->groupBy(fn ($item) => $item->fase?->value ?? 'sem_fase')
            ->map(fn ($grupo) => $grupo
                ->groupBy(fn ($item) => $item->tipo_contrato->value)
                ->map(fn ($subgrupo) => $subgrupo->keyBy(fn ($item) => $item->tipo_documento->value))
            );

        // Mapeamento padrao para pre-popular a UI
        $mapeamentoPadrao = ChecklistService::MAPEAMENTO_FASE_DOCUMENTO;

        return view('tenant.configuracoes-checklist.index', compact(
            'tiposContrato',
            'tiposDocumento',
            'fases',
            'configuracoes',
            'mapeamentoPadrao',
        ));
    }

    /**
     * Salva o checklist configurado (matriz de checkboxes por fase).
     */
    public function update(Request $request): RedirectResponse
    {
        $checklist = $request->input('checklist', []);

        foreach (FaseContratual::cases() as $fase) {
            $documentosDaFase = ChecklistService::MAPEAMENTO_FASE_DOCUMENTO[$fase->value] ?? [];

            foreach (TipoContrato::cases() as $tipoContrato) {
                foreach ($documentosDaFase as $tipoDocumento) {
                    $isAtivo = ! empty($checklist[$fase->value][$tipoContrato->value][$tipoDocumento->value]);

                    ConfiguracaoChecklistDocumento::updateOrCreate(
                        [
                            'fase' => $fase->value,
                            'tipo_contrato' => $tipoContrato->value,
                            'tipo_documento' => $tipoDocumento->value,
                        ],
                        ['is_ativo' => $isAtivo]
                    );
                }
            }
        }

        DocumentoService::limparCacheChecklist();

        return redirect()->route('tenant.configuracoes-checklist.index')
            ->with('success', 'Checklist de documentos obrigatorios atualizado com sucesso.');
    }
}
