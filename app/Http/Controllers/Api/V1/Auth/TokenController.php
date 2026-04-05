<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TokenController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $tokens = $request->user()->tokens()->latest()->get()->map(fn ($token) => [
            'id' => $token->id,
            'name' => $token->name,
            'abilities' => $token->abilities,
            'last_used_at' => $token->last_used_at,
            'created_at' => $token->created_at,
        ]);

        return response()->json([
            'data' => $tokens,
        ]);
    }

    public function destroy(Request $request, int $tokenId): JsonResponse
    {
        $deleted = $request->user()->tokens()->whereKey($tokenId)->delete();

        if (! $deleted) {
            return response()->json([
                'message' => 'Token no encontrado.',
            ], 404);
        }

        return response()->json([
            'message' => 'Token revocado correctamente.',
        ]);
    }
}
