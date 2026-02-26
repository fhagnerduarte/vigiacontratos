<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function indicadores(): JsonResponse
    {
        $dados = DashboardService::obterDadosCacheados();

        return response()->json([
            'indicadores' => $dados['indicadores'] ?? [],
            'score_gestao' => $dados['score_gestao'] ?? null,
            'vencimentos' => $dados['vencimentos'] ?? [],
            'ranking_secretarias' => $dados['ranking_secretarias'] ?? [],
        ]);
    }
}
