<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PushNotificationService;
use Illuminate\Http\Request;

class PushController extends Controller
{
    protected $pushService;

    public function __construct(PushNotificationService $pushService)
    {
        $this->pushService = $pushService;
    }

    public function send(Request $request)
    {
        $request->validate([
            'title' => 'required|string',
            'body' => 'required|string',
            'tenant_id' => 'sometimes|integer',
        ]);

        $tenantId = $request->tenant_id ?? ($request->user()->tenant_id ?? 1);

        $this->pushService->broadcastToTenant(
            $tenantId,
            $request->title,
            $request->body
        );

        return response()->json(['message' => 'Push notification sent']);
    }
}