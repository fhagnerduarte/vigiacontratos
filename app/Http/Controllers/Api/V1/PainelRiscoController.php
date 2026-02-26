<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\PainelRiscoService;
use Illuminate\Http\JsonResponse;

class PainelRiscoController extends Controller
{
    public function indicadores(PainelRiscoService $painelRiscoService): JsonResponse
    {
        $indicadores = $painelRiscoService->indicadores();

        return response()->json(['indicadores' => $indicadores]);
    }

    public function ranking(PainelRiscoService $painelRiscoService): JsonResponse
    {
        $ranking = $painelRiscoService->rankingRisco();

        return response()->json(['ranking' => $ranking]);
    }
}
