<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Webhook;
use App\Services\WebhookService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WebhooksController extends Controller
{
    public function index(): JsonResponse
    {
        $webhooks = Webhook::orderByDesc('created_at')->get();

        return response()->json(['data' => $webhooks]);
    }

    public function show(int $id): JsonResponse
    {
        $webhook = Webhook::findOrFail($id);

        return response()->json(['data' => $webhook]);
    }

    public function store(Request $request): JsonResponse
    {
        $dados = $request->validate([
            'url' => ['required', 'url', 'max:500'],
            'eventos' => ['required', 'array', 'min:1'],
            'eventos.*' => ['required', 'string', 'in:' . implode(',', WebhookService::EVENTOS_DISPONIVEIS)],
            'descricao' => ['nullable', 'string', 'max:255'],
        ]);

        $dados['secret'] = WebhookService::gerarSecret();
        $dados['is_ativo'] = true;

        $webhook = Webhook::create($dados);

        return response()->json([
            'data' => $webhook,
            'secret' => $webhook->secret,
            'message' => 'Webhook criado. Salve o secret — ele nao sera exibido novamente.',
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $webhook = Webhook::findOrFail($id);

        $dados = $request->validate([
            'url' => ['sometimes', 'url', 'max:500'],
            'eventos' => ['sometimes', 'array', 'min:1'],
            'eventos.*' => ['required', 'string', 'in:' . implode(',', WebhookService::EVENTOS_DISPONIVEIS)],
            'descricao' => ['nullable', 'string', 'max:255'],
            'is_ativo' => ['sometimes', 'boolean'],
        ]);

        $webhook->update($dados);

        return response()->json(['data' => $webhook->fresh()]);
    }

    public function destroy(int $id): JsonResponse
    {
        $webhook = Webhook::findOrFail($id);
        $webhook->delete();

        return response()->json(null, 204);
    }

    public function eventos(): JsonResponse
    {
        return response()->json([
            'eventos' => WebhookService::EVENTOS_DISPONIVEIS,
        ]);
    }
}
