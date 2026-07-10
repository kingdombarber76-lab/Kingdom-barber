<?php

namespace App\Services;

use App\Models\PushToken;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PushNotificationService
{
    private string $fcmUrl = 'https://fcm.googleapis.com/v1/projects/{PROJECT_ID}/messages:send';

    public function notifyBarber(int $barberId, string $title, string $body, array $data = []): void
    {
        $this->sendToUser(userId: $barberId, title: $title, body: $body, data: $data);
    }

    public function notifyUser(int $userId, string $title, string $body, array $data = []): void
    {
        $this->sendToUser(userId: $userId, title: $title, body: $body, data: $data);
    }

    public function broadcastToTenant(int $tenantId, string $title, string $body, array $data = []): void
    {
        // CORRECCIÓN: Quitamos el punto y usamos la flecha -> para llamar al método
        $tokens = PushToken::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->pluck('token')
            ->toArray(); // <--- AQUÍ ESTABA EL ERROR

        foreach (array_chunk($tokens, 500) as $chunk) {
            $this->sendToTokens($chunk, $title, $body, $data);
        }
    }

    private function sendToUser(int $userId, string $title, string $body, array $data): void
    {
        // CORRECCIÓN: Quitamos el punto y usamos la flecha -> para llamar al método
        $tokens = PushToken::where('user_id', $userId)
            ->where('is_active', true)
            ->pluck('token')
            ->toArray(); // <--- AQUÍ ESTABA EL ERROR

        if (!empty($tokens)) {
            $this->sendToTokens($tokens, $title, $body, $data);
        }
    }

    private function sendToTokens(array $tokens, string $title, string $body, array $data): void
    {
        foreach ($tokens as $token) {
            try {
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $this->getAccessToken(),
                    'Content-Type'  => 'application/json',
                ])->post(
                    str_replace('{PROJECT_ID}', config('firebase.project_id'), $this->fcmUrl),
                    [
                        'message' => [
                            'token' => $token,
                            'notification' => ['title' => $title, 'body' => $body],
                            'data' => array_map('strval', $data),
                            'android' => ['priority' => 'high'],
                            'apns' => [
                                'payload' => [
                                    'aps' => [
                                        'sound' => 'default',
                                    ],
                                ],
                            ],
                        ],
                    ]
                );

                if ($response->failed()) {
                    Log::warning('FCM error for token ' . substr($token, 0, 20), $response->json());

                    if (str_contains($response->body(), 'UNREGISTERED')) {
                        PushToken::where('token', $token)->update(['is_active' => false]);
                    }
                }
            } catch (\Exception $e) {
                Log::error('Push notification failed: ' . $e->getMessage());
            }
        }
    }

    private function getAccessToken(): string
    {
        return app('firebase.messaging')->getToken();
    }
}