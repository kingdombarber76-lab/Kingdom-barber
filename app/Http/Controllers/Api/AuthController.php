<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Google_Client;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        // Implementation as per plan (omitted for brevity)
        // You can add the register logic from the plan if needed.
        return response()->json(['message' => 'Registered'], 201);
    }

    public function login(Request $request): JsonResponse
    {
        // Implementation as per plan
        return response()->json(['message' => 'Logged in'], 200);
    }

    public function googleCallback(Request $request): JsonResponse
    {
        $request->validate(['token' => 'required|string']);

        try {
            $client = new Google_Client(['client_id' => config('services.google.client_id')]);
            $payload = $client->verifyIdToken($request->token);

            if (!$payload) {
                return response()->json(['message' => 'Invalid token'], 401);
            }

            $user = User::firstOrCreate(
                ['google_id' => $payload['sub']],
                [
                    'tenant_id'      => 1, // default tenant for MVP
                    'name'           => $payload['name'],
                    'email'          => $payload['email'],
                    'avatar_url'     => $payload['picture'],
                    'oauth_provider' => 'google',
                    'role'           => $payload['email'] === config('services.google.owner_email') ? 'owner' : 'client',
                    'password'       => bcrypt(Str::random(16)), // random password for OAuth users
                    'is_active'      => true,
                ]
            );

            $token = $user->createToken('mobile-app')->plainTextToken;

            return response()->json([
                'token' => $token,
                'user'  => $user->only(['id', 'name', 'email', 'avatar_url', 'role']),
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Authentication error'], 500);
        }
    }

    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();
        if ($user && $user->currentAccessToken()) {
            $user->currentAccessToken()->delete();
        }

        return response()->json(['message' => 'Logged out']);
    }

    public function refresh(Request $request): JsonResponse
    {
        $user = $request->user();

        $token = $user->createToken('mobile-app')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user'  => $user->only(['id', 'name', 'email', 'avatar_url', 'role']),
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json($request->user()->only(['id', 'name', 'email', 'avatar_url', 'role']));
    }
}