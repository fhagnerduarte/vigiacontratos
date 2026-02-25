<?php

namespace App\Http\Controllers\Tenant;

use App\Enums\TipoContrato;
use App\Enums\TipoDocumentoContratual;
use App\Http\Controllers\Controller;
use App\Models\ConfiguracaoChecklistDocumento;
use App\Services\DocumentoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ConfiguracaoChecklistDocumentoController extends Controller
{
    /**
     * Exibe matriz de configuracao do checklist (tipo_contrato x tipo_documento).
     */
    public function index(): View
    {
        $tiposContrato = TipoContrato::cases();
        $tiposDocumento = TipoDocumentoContratual::cases();

        $configuracoes = ConfiguracaoChecklistDocumento::all()
            ->groupBy(fn ($item) => $item->tipo_contrato->value)
            ->map(fn ($grupo) => $grupo->keyBy(fn ($item) => $item->tipo_documento->value));

        return view('tenant.configuracoes-checklist.index', compact(
            'tiposContrato',
            'tiposDocumento',
            'configuracoes',
        ));
    }

    /**
     * Salva o checklist configurado (matriz de checkboxes).
     */
    public function update(Request $request): RedirectResponse
    {
        $checklist = $request->input('checklist', []);

        foreach (TipoContrato::cases() as $tipoContrato) {
            foreach (TipoDocumentoContratual::cases() as $tipoDocumento) {
                $isAtivo = ! empty($checklist[$tipoContrato->value][$tipoDocumento->value]);

                ConfiguracaoChecklistDocumento::updateOrCreate(
                    [
                        'tipo_contrato' => $tipoContrato->value,
                        'tipo_documento' => $tipoDocumento->value,
                    ],
                    ['is_ativo' => $isAtivo]
                );
            }
        }

        DocumentoService::limparCacheChecklist();

        return redirect()->route('tenant.configuracoes-checklist.index')
            ->with('success', 'Checklist de documentos obrigatorios atualizado com sucesso.');
    }
}
