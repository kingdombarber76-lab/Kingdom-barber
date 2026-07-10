<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Service;
use App\Models\User;
use App\Services\AvailabilityService;
use App\Services\PushNotificationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AppointmentController extends Controller
{
    public function __construct(
        private AvailabilityService $availability,
        private PushNotificationService $push
    ) {}

    // GET /api/v1/appointments/available
    public function available(Request $request): JsonResponse
    {
        $request->validate([
            'date'       => 'required|date|after_or_equal:today',
            'service_id' => 'required|integer|exists:services,id',
            'barber_id'  => 'required|integer|exists:users,id',
        ]);

        $service = Service::findOrFail($request->service_id);
        $barber  = User::findOrFail($request->barber_id);

        $slots = $this->availability->getAvailableSlots(
            tenantId: $barber->tenant_id,
            barberId: $barber->id,
            date: $request->date,
            duration: $service->duration_minutes
        );

        return response()->json(['slots' => $slots]);
    }

    // POST /api/v1/appointments
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'service_id'   => 'required|integer|exists:services,id',
            'barber_id'    => 'required|integer|exists:users,id',
            'date'         => 'required|date|after_or_equal:today',
            'time'         => 'required|date_format:H:i',
            'client_name'  => 'required_without:client_id|string|max:120',
            'client_phone' => 'required_without:client_id|string|max:30',
            'client_email' => 'nullable|email',
            'notes'        => 'nullable|string|max:500',
        ]);

        $service = Service::findOrFail($data['service_id']);
        $barber  = User::findOrFail($data['barber_id']);

        $start = Carbon::createFromFormat('Y-m-d H:i', "{$data['date']} {$data['time']}");
        $end   = $start->copy()->addMinutes($service->duration_minutes);

        $appointment = null;

        DB::transaction(function () use ($barber, $data, $service, $start, $end, &$appointment) {

            $conflict = Appointment::where('tenant_id', $barber->tenant_id)
                ->where('barber_id', $barber->id)
                ->where('appointment_date', $data['date'])
                ->whereNotIn('status', ['cancelled'])
                ->where(function ($q) use ($start, $end) {
                    $q->where('start_time', '<', $end->format('H:i:s'))
                      ->where('end_time', '>', $start->format('H:i:s'));
                })
                ->lockForUpdate()
                ->exists();

            if ($conflict) {
                abort(409, 'El horario ya no está disponible.');
            }

            $appointment = Appointment::create([
                'tenant_id'        => $barber->tenant_id,
                'service_id'       => $service->id,
                'barber_id'        => $barber->id,
                'client_id'        => auth()->id(),
                'client_name'      => $data['client_name'] ?? null,
                'client_phone'     => $data['client_phone'] ?? null,
                'client_email'     => $data['client_email'] ?? null,
                'appointment_date' => $data['date'],
                'start_time'       => $start->format('H:i:s'),
                'end_time'         => $end->format('H:i:s'),
                'status'           => 'pending',
                'notes'            => $data['notes'] ?? null,
                'total_price'      => $service->price,
            ]);
        });

        $clientName = $appointment->client_name ?? 'Cliente';

        $this->push->notifyBarber(
            barberId: $barber->id,
            title: '📅 Nueva reserva',
            body: "{$clientName} — {$service->name} a las {$data['time']}",
        );

        return response()->json([
            'message'     => 'Reserva creada exitosamente.',
            'appointment' => $appointment->load('service', 'barber'),
        ], 201);
    }

    // GET /api/v1/appointments
    public function index(Request $request): JsonResponse
    {
        // Prioridad: 1) tenant_id explícito por query param
        //            2) tenant del usuario autenticado
        //            3) fallback a 1 (compatibilidad hacia atrás)
        $request->validate([
            'tenant_id' => 'nullable|integer|exists:tenants,id',
        ]);

        $tenantId = $request->filled('tenant_id')
            ? (int) $request->tenant_id
            : (auth()->check() ? auth()->user()->tenant_id : 1);

        $query = Appointment::query()
            ->where('tenant_id', $tenantId);

        if ($request->filled('date')) {
            $query->where('appointment_date', $request->date);
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('appointment_date', [
                $request->start_date,
                $request->end_date
            ]);
        }

        return response()->json(
            $query->with(['service', 'barber'])
                  ->orderBy('appointment_date')
                  ->paginate(50)
        );
    }

    // PATCH /api/v1/admin/appointments/{uuid}/status
    public function updateStatus(Request $request, string $uuid): JsonResponse
    {
        $appointment = Appointment::where('uuid', $uuid)->firstOrFail();

        $data = $request->validate([
            'status'         => 'required|in:confirmed,attended,cancelled,no_show',
            'internal_notes' => 'nullable|string|max:500',
        ]);

        $appointment->update($data);

        if ($appointment->client_id && in_array($data['status'], ['confirmed', 'cancelled'])) {

            $msg = match ($data['status']) {
                'confirmed' => '✅ Tu turno fue confirmado',
                'cancelled' => '❌ Tu turno fue cancelado',
                default => null
            };

            if ($msg) {
                $tenantName = optional($appointment->barber?->tenant)->name ?? 'Tu salón';

                $this->push->notifyUser(
                    userId: $appointment->client_id,
                    title: $tenantName,
                    body: $msg . ' — ' . $appointment->appointment_date . ' ' . substr($appointment->start_time, 0, 5),
                );
            }
        }

        return response()->json(['appointment' => $appointment]);
    }
}
