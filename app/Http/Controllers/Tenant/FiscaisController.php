<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreFiscalRequest;
use App\Models\Contrato;
use App\Services\AuditoriaService;
use App\Services\FiscalService;
use Illuminate\Http\RedirectResponse;

class FiscaisController extends Controller
{
    public function store(StoreFiscalRequest $request, Contrato $contrato): RedirectResponse
    {
        $dados = $request->validated();

        // Verifica se ja existe fiscal atual — se sim, e troca (RN-034)
        if ($contrato->fiscalAtual) {
            $fiscalAnterior = $contrato->fiscalAtual->nome;
            $novoFiscal = FiscalService::trocar($contrato, $dados);

            // Auditoria da troca de fiscal (RN-036)
            AuditoriaService::registrar(
                $contrato,
                'fiscal_atual',
                $fiscalAnterior,
                $novoFiscal->nome,
                $request->user(),
                $request->ip()
            );

            return redirect()->route('tenant.contratos.show', $contrato)
                ->with('success', 'Fiscal trocado com sucesso. O fiscal anterior foi mantido no historico.');
        }

        // Primeira designacao
        FiscalService::designar($contrato, $dados);

        return redirect()->route('tenant.contratos.show', $contrato)
            ->with('success', 'Fiscal designado com sucesso.');
    }
}
