<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Promotion;
use Illuminate\Http\Request;

class PromotionController extends Controller
{
    public function index()
    {
        $tenantId = auth()->user()?->tenant_id ?? 1;
        return Promotion::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->whereDate('starts_at', '<=', today())
            ->whereDate('ends_at', '>=', today())
            ->get();
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string',
            'type' => 'required|in:discount_percent,discount_fixed,free_service,info',
            'starts_at' => 'required|date',
            'ends_at' => 'required|date|after_or_equal:starts_at',
        ]);

        $promotion = Promotion::create([
            'tenant_id' => $request->user()->tenant_id,
            'title' => $request->title,
            'description' => $request->description,
            'image_url' => $request->image_url,
            'type' => $request->type,
            'discount_value' => $request->discount_value,
            'service_id' => $request->service_id,
            'starts_at' => $request->starts_at,
            'ends_at' => $request->ends_at,
            'send_push' => $request->send_push ?? false,
            'is_active' => $request->is_active ?? true,
        ]);

        // Optionally send push notification if send_push is true
        if ($request->send_push) {
            // TODO: trigger push notification
        }

        return response()->json($promotion, 201);
    }

    public function update(Request $request, $id)
    {
        $promotion = Promotion::findOrFail($id);
        if ($promotion->tenant_id !== $request->user()->tenant_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $promotion->update($request->all());
        return response()->json($promotion);
    }

    public function destroy($id)
    {
        $promotion = Promotion::findOrFail($id);
        if ($promotion->tenant_id !== request()->user()->tenant_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        $promotion->delete();
        return response()->json(['message' => 'Deleted']);
    }
}