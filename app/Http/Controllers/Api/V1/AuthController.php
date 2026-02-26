<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\SecretariaResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
            'device_name' => 'required|string|max:255',
            'abilities' => 'sometimes|array',
            'abilities.*' => 'string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Credenciais invalidas.'],
            ]);
        }

        if (! $user->is_ativo) {
            throw ValidationException::withMessages([
                'email' => ['Usuario inativo.'],
            ]);
        }

        $abilities = $request->input('abilities', ['*']);

        $token = $user->createToken(
            $request->device_name,
            $abilities,
        );

        return response()->json([
            'token' => $token->plainTextToken,
            'user' => [
                'id' => $user->id,
                'nome' => $user->nome,
                'email' => $user->email,
                'role' => $user->role?->nome,
            ],
        ], 201);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Token revogado com sucesso.',
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->load('role', 'secretarias');

        return response()->json([
            'id' => $user->id,
            'nome' => $user->nome,
            'email' => $user->email,
            'role' => $user->role ? [
                'id' => $user->role->id,
                'nome' => $user->role->nome,
                'descricao' => $user->role->descricao,
            ] : null,
            'secretarias' => SecretariaResource::collection($user->secretarias),
            'is_perfil_estrategico' => $user->isPerfilEstrategico(),
            'mfa_enabled' => $user->isMfaEnabled(),
        ]);
    }

    public function tokens(Request $request): JsonResponse
    {
        $tokens = $request->user()->tokens()
            ->orderByDesc('last_used_at')
            ->get()
            ->map(fn ($token) => [
                'id' => $token->id,
                'name' => $token->name,
                'abilities' => $token->abilities,
                'last_used_at' => $token->last_used_at?->toIso8601String(),
                'created_at' => $token->created_at->toIso8601String(),
            ]);

        return response()->json(['tokens' => $tokens]);
    }

    public function revokeToken(Request $request, int $tokenId): JsonResponse
    {
        $token = $request->user()->tokens()->find($tokenId);

        if (! $token) {
            return response()->json(['message' => 'Token nao encontrado.'], 404);
        }

        $token->delete();

        return response()->json(['message' => 'Token revogado com sucesso.']);
    }
}
