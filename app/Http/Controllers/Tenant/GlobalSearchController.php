<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Contrato;
use App\Models\Fornecedor;
use App\Models\Servidor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class GlobalSearchController extends Controller
{
    public function search(Request $request): JsonResponse
    {
        $query = trim($request->input('q', ''));

        if (mb_strlen($query) < 2) {
            return response()->json([]);
        }

        $results = [];
        $term = '%' . $query . '%';

        // Contratos: número, objeto
        if ($request->user()->hasPermission('contrato.visualizar')) {
            $contratos = Contrato::where('numero', 'like', $term)
                ->orWhere('objeto', 'like', $term)
                ->limit(5)
                ->get(['id', 'numero', 'ano', 'objeto']);

            foreach ($contratos as $c) {
                $results[] = [
                    'type' => 'Contratos',
                    'icon' => 'solar:document-bold',
                    'label' => $c->numero . '/' . $c->ano,
                    'sublabel' => Str::limit($c->objeto, 50),
                    'url' => route('tenant.contratos.show', $c->id),
                ];
            }
        }

        // Fornecedores: razão social, cnpj
        if ($request->user()->hasPermission('fornecedor.visualizar')) {
            $fornecedores = Fornecedor::where('razao_social', 'like', $term)
                ->orWhere('cnpj', 'like', $term)
                ->limit(5)
                ->get(['id', 'razao_social', 'cnpj']);

            foreach ($fornecedores as $f) {
                $results[] = [
                    'type' => 'Fornecedores',
                    'icon' => 'solar:buildings-bold',
                    'label' => Str::limit($f->razao_social, 40),
                    'sublabel' => $f->cnpj,
                    'url' => route('tenant.fornecedores.edit', $f->id),
                ];
            }
        }

        // Servidores: nome, matrícula
        if ($request->user()->hasPermission('servidor.visualizar')) {
            $servidores = Servidor::where('nome', 'like', $term)
                ->orWhere('matricula', 'like', $term)
                ->limit(5)
                ->get(['id', 'nome', 'matricula']);

            foreach ($servidores as $s) {
                $results[] = [
                    'type' => 'Servidores',
                    'icon' => 'solar:user-bold',
                    'label' => Str::limit($s->nome, 40),
                    'sublabel' => 'Mat. ' . $s->matricula,
                    'url' => route('tenant.servidores.edit', $s->id),
                ];
            }
        }

        return response()->json($results);
    }
}
