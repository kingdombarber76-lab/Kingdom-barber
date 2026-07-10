<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids; // 1. Importamos la función nativa de UUIDs
use Illuminate\Support\Str;

class Appointment extends Model
{
    // 2. Agregamos HasUuids acá
    use SoftDeletes, HasUuids;

    // 3. Le indicamos a Laravel que la clave primaria (id) NO es un número autoincremental, sino un texto (UUID)
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'uuid',
        'tenant_id',
        'service_id',
        'barber_id',
        'client_id',
        'client_name',
        'client_phone',
        'client_email',
        'appointment_date',
        'start_time',
        'end_time',
        'status',
        'notes',
        'internal_notes',
        'total_price',
        'reminder_sent_at',
    ];

    protected $dates = [
        'reminder_sent_at',
        'deleted_at',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($appointment) {
            // Mantenemos esto por si tu controlador/frontend necesita sí o sí 
            // la columna secundaria llamada 'uuid' que creamos en la migración.
            if (empty($appointment->uuid)) {
                $appointment->uuid = (string) Str::uuid();
            }
        });
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function barber()
    {
        return $this->belongsTo(User::class, 'barber_id');
    }

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    // Scope for upcoming appointments
    public function scopeUpcoming($query)
    {
        return $query->where('appointment_date', '>=', today())
            ->whereIn('status', ['pending', 'confirmed']);
    }
}