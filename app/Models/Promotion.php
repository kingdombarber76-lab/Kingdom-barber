<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Promotion extends Model
{
    protected $fillable = [
        'tenant_id',
        'title',
        'description',
        'image_url',
        'type',
        'discount_value',
        'service_id',
        'starts_at',
        'ends_at',
        'send_push',
        'is_active',
    ];

    protected $casts = [
        'send_push' => 'boolean',
        'is_active' => 'boolean',
        'starts_at' => 'date',
        'ends_at' => 'date',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}