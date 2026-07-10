<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class BarberController extends Controller
{
    public function index()
    {
        // Only barbers (and maybe owners) for the tenant
        $tenantId = auth()->user()?->tenant_id ?? 1;
        return User::where('tenant_id', $tenantId)
            ->whereIn('role', ['barber', 'owner'])
            ->get(['id', 'name', 'email', 'phone', 'avatar_url']);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'phone' => 'nullable|string',
            'role' => 'required|in:barber,owner',
        ]);

        $user = User::create([
            'tenant_id' => $request->user()->tenant_id,
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'role' => $request->role,
            'is_active' => true,
        ]);

        return response()->json($user, 201);
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        if ($user->tenant_id !== $request->user()->tenant_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $data = $request->only(['name', 'email', 'phone', 'role']);
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);
        return response()->json($user);
    }

    public function schedule($id)
    {
        $user = User::findOrFail($id);
        if ($user->tenant_id !== auth()->user()->tenant_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        return $user->sessions; // assuming relationship 'schedules' but we have 'schedules'
    }

    public function updateSchedule(Request $request, $id)
    {
        $user = User::findOrFail($id);
        if ($user->tenant_id !== auth()->user()->tenant_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'day_of_week' => 'required|integer|between:0,6',
            'opens_at' => 'required|date_format:H:i',
            'closes_at' => 'required|date_format:H:i|after:opens_at',
        ]);

        // Delete existing schedule for that day (simple approach)
        $user->schedules()->where('day_of_week', $request->day_of_week)->delete();

        $schedule = $user->schedules()->create([
            'tenant_id' => $user->tenant_id,
            'day_of_week' => $request->day_of_week,
            'opens_at' => $request->opens_at,
            'closes_at' => $request->closes_at,
            'is_active' => true,
        ]);

        return response()->json($schedule, 201);
    }
}