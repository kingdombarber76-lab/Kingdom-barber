<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function index()
{
    return Service::all();
}

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'duration_minutes' => 'required|integer',
            'price' => 'required|numeric',
            // ... other fields
        ]);

        $service = Service::create([
            'tenant_id' => $request->user()->tenant_id,
            'name' => $request->name,
            'description' => $request->description,
            'duration_minutes' => $request->duration_minutes,
            'price' => $request->price,
            'price_old' => $request->price_old,
            'is_featured' => $request->is_featured ?? false,
            'is_active' => $request->is_active ?? true,
            'sort_order' => $request->sort_order ?? 0,
        ]);

        return response()->json($service, 201);
    }

    public function update(Request $request, $id)
    {
        $service = Service::findOrFail($id);
        // Ensure ownership
        if ($service->tenant_id !== $request->user()->tenant_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $service->update($request->all());
        return response()->json($service);
    }

    public function destroy($id)
    {
        $service = Service::findOrFail($id);
        if ($service->tenant_id !== request()->user()->tenant_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        $service->delete();
        return response()->json(['message' => 'Deleted']);
    }
}